<?php

namespace App\Services\Order;

use App\Events\Order\OrderAddedNewProductEvent;
use App\Events\Order\OrderCompletedEvent;
use App\Events\Order\OrderCreatedEvent;
use App\Events\Order\OrderProcessedEvent;
use App\Events\Order\OrderSubmittedEvent;
use App\Exceptions\InvalidDataException;
use App\Exceptions\ServerErrorException;
use App\Models\CompletedOrder;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\UserWallet;
use App\Services\GenerateOrderCode;
use App\Services\Status\StatusService;
use App\Services\Utils\DateFormatter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    private string|null $startDate = null;
    private string|null $endDate = null;

    public function setDate(string $start, string $end): void
    {
        $this->startDate = DateFormatter::format($start, 'start');
        $this->endDate = DateFormatter::format($end, 'end');
    }

    public function findAll($statusCode): array
    {
        // Auth User
        $authUser = FacadesAuth::user();

        // Query
        $query = Order::with(
            'user',
            'customer',
            'status'
        );


        if ($statusCode) {
            $status = StatusService::findByCode($statusCode);

            // Submitted
            if ($statusCode === 'orderSubmitted' || $statusCode === 'orderCompleted') {
                $query->with('completedOrder');
            } else if ($statusCode === 'orderCancel') {
                $query->with('cancelOrder');
            }

            $query->where('status_id', $status->id);
        }

        if (!$authUser->isAdmin()) {
            $query->where('user_id', $authUser->id);
        }

        $data = $query
            ->orderByDesc('created_at')
            ->whereBetween('updated_at', [$this->startDate, $this->endDate])
            ->get();

        // Totals
        $totals = $this->getTotalsByStatusCode($statusCode);

        return [
            'data' => $data,
            'total_sale_price' => $totals['total_amount'],
            'total_count' => $totals['total_count'],
        ];
    }

    /**
     * @throws ServerErrorException
     */
    public function create(array $data): array
    {
        // Auth User
        $authUser = Auth::user();

        // Req Body
        $customerId = $data['customer_id'];
        $productList = $data['product_list'];

        // New Order status
        $newOrderStatus = StatusService::findByCode('orderNew');

        // Get Request Products
        $productsId = array_column($productList ?? [], 'product_id');
        $products = Product::whereIn('id', $productsId)->get();

        $pluckIsChangePrice = $products->pluck('is_change_price', 'id');
        $pluckedCostPrice = $products->pluck('cost_price', 'id');
        $pluckedSalePrice = $products->pluck('sale_price', 'id');

        // Generate Order Code
        $orderCode = GenerateOrderCode::generate($customerId);

        DB::beginTransaction();

        try {
            $newOrder = Order::create([
                "user_id" => $authUser->id,
                "customer_id" => $customerId,
                "status_id" => $newOrderStatus->id,
                'total_cost_price' => 0,
                'total_sale_price' => 0,
                'ord_code' => $orderCode
            ]);


            $totalCostPrice = 0;
            $totalSalePrice = 0;

            // Create Order Details
            $detailItemList = [];
            foreach ($productList as $item) {
                // Req Item
                $productId = $item['product_id'];
                $amount = $item['amount'];
                $amountTypeId = $item['amount_type_id'];
                $salePriceAmount = $item['sale_price_amount'] ?? null;

                // Prices
                $costPrice = $pluckedCostPrice[$productId];
                $salePrice = $pluckIsChangePrice[$productId] && $salePriceAmount ? $salePriceAmount : $pluckedSalePrice[$productId];
                $sumCostPrice = $costPrice * $amount;
                $sumSalePrice = $salePrice * $amount;

                $detailItemList[] = [
                    'product_id' => $productId,
                    'amount' => $amount,
                    'amount_type_id' => $amountTypeId,
                    'cost_price' => $costPrice,
                    'sale_price' => $salePrice,
                    'sum_cost_price' => $sumCostPrice,
                    'sum_sale_price' => $sumSalePrice,
                ];

                $totalCostPrice += $sumCostPrice;
                $totalSalePrice += $sumSalePrice;
            }

            $newOrder->orderDetails()->createMany($detailItemList);


            $newOrder->total_cost_price = $totalCostPrice;
            $newOrder->total_sale_price = $totalSalePrice;

            $newOrder->save();

            DB::commit();

            // Event
            OrderCreatedEvent::dispatch($newOrder);

            return [
                "message" => "Yangi buyurtma muvaffaqiyatli qo'shildi!",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function createSalePos(array $data): array
    {
        // Auth User
        $authUser = Auth::user();

        // Req Body
        $customerId = $data['customer_id'];
        $productList = $data['product_list'];
        $payment = $data['payment'];

        // New Order status
        $newOrderStatus = StatusService::findByCode('orderNew');

        // Customer
        $customer = Customer::findOrFail($customerId);

        // Get Request Products
        $productsId = array_column($productList ?? [], 'product_id');
        $products = Product::whereIn('id', $productsId)->get();

        $pluckIsChangePrice = $products->pluck('is_change_price', 'id');
        $pluckedCostPrice = $products->pluck('cost_price', 'id');
        $pluckedSalePrice = $products->pluck('sale_price', 'id');

        // User Wallet
        $userWallet = UserWallet::find($payment['user_wallet_id']);

        // Generate Order Code
        $orderCode = GenerateOrderCode::generate($customerId);

        // Status Payment Debt Customer
        $statusDebtCustomer = StatusService::findByCode('paymentCustomer');

        DB::beginTransaction();

        try {
            $newOrder = Order::create([
                "user_id" => $authUser->id,
                "customer_id" => $customer->id,
                "status_id" => $newOrderStatus->id,
                'total_cost_price' => 0,
                'total_sale_price' => 0,
                'ord_code' => $orderCode
            ]);


            $totalCostPrice = 0;
            $totalSalePrice = 0;

            // Create Order Details
            $detailItemList = [];
            foreach ($productList as $item) {
                // Req Item
                $productId = $item['product_id'];
                $amount = $item['amount'];
                $amountTypeId = $item['amount_type_id'];
                $salePriceAmount = $item['sale_price_amount'] ?? null;

                // Prices
                $costPrice = $pluckedCostPrice[$productId];
                $salePrice = $pluckIsChangePrice[$productId] && $salePriceAmount ? $salePriceAmount : $pluckedSalePrice[$productId];
                $sumCostPrice = $costPrice * $amount;
                $sumSalePrice = $salePrice * $amount;

                $detailItemList[] = [
                    'product_id' => $productId,
                    'amount' => $amount,
                    'amount_type_id' => $amountTypeId,
                    'cost_price' => $costPrice,
                    'sale_price' => $salePrice,
                    'sum_cost_price' => $sumCostPrice,
                    'sum_sale_price' => $sumSalePrice,
                ];

                $totalCostPrice += $sumCostPrice;
                $totalSalePrice += $sumSalePrice;
            }

            // Create Many Items
            $newOrder->orderDetails()->createMany($detailItemList);


            // Change Totals
            $newOrder->total_cost_price = $totalCostPrice;
            $newOrder->total_sale_price = $totalSalePrice;

            $newOrder->save();

            // Payment
            $newPayment = new Payment([
                'user_id' => $authUser->id,
                'status_id' => $statusDebtCustomer->id,
            ]);

            $customer->payments()->save($newPayment);

            // Sum Amount
            $sumAmount = $payment['amount'] * $payment['currency_amount'];

            // Wallet Attach 
            $newPayment->wallets()->attach($userWallet->wallet_id, [
                'amount' => $payment['amount'],
                'rate_amount' => $payment['currency_amount'],
                'sum_price' => $sumAmount,
                'updated_at' => now(),
                'created_at' => now(),
            ]);

            // Change User wallet amount
            $userWallet->increment('amount', $payment['amount']);


            // Change Customer Balance
            $customer->increment('balance', $sumAmount);

            DB::commit();

            // Event
            OrderCreatedEvent::dispatch($newOrder);

            $formatVal = number_format($sumAmount);

            return [
                "message" => "Yangi buyurtma muvaffaqiyatli qo'shildi! {$customer->first_name}.{$customer->last_name[0]} mijozdan {$formatVal} so'm o'tkazma qabul qilindi",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function findOne(int $id): array
    {
        // Auth User
        $authUser = Auth::user();

        $query = Order::with(
            'user',
            'customer',
            'status',
            'orderDetails',
            'cancelOrder',
            'completedOrder'
        )->where('id', $id);

        if (!$authUser->isAdmin()) {
            $query->where('user_id', $authUser->id);
        }

        $order = $query->firstOrFail();

        return [
            "data" => $order,
        ];
    }

    public function confirm(int $id): array
    {
        $authUser = Auth::user();

        $newOrderStatus = StatusService::findByCode('orderNew');

        $order = Order::where('user_id', $authUser->id)
            ->where('status_id', $newOrderStatus->id)
            ->findOrFail($id);

        // Status Code
        $statusInProgress = StatusService::findByCode('orderInProgress');

        $order->status_id = $statusInProgress->id;
        $order->save();

        // Event
        OrderProcessedEvent::dispatch($order);

        return [
            'message' => "Buyurtma tasdiqlandi va hozir jarayonda",
            'data' => [
                'status' => $statusInProgress
            ]
        ];
    }

    /**
     * @throws ServerErrorException
     * @throws InvalidDataException
     */
    public function addProduct(array $data, int $id): array
    {
        $authUser = Auth::user();

        // Req Body
        $productId = $data['product_id'];
        $amount = $data['amount'];
        $amountTypeId = $data['amount_type_id'];
        $salePriceAmount = $data['sale_price_amount'] ?? null;


        // Product
        $product = Product::findOrFail($productId);

        // Status
        $statusOrderInProgress = StatusService::findByCode('orderInProgress');

        // Order
        $order = Order::where('user_id', $authUser->id)
            ->where('status_id', $statusOrderInProgress->id)
            ->findOrFail($id);

        // Validate Order Details
        $detailItemExists = OrderDetail::where('order_id', $order->id)
            ->where('product_id', $productId)
            ->exists();
        if ($detailItemExists) {
            throw new InvalidDataException("Bu mahsulot allaqachon mavjud");
        }

        // Product Price
        $productSalePrice = $product->is_change_price && $salePriceAmount
            ? $salePriceAmount
            : $product->sale_price;

        // Create Order Details Item
        $sumCostPrice = $product->cost_price * $amount;
        $sumSalePrice = $productSalePrice * $amount;

        DB::beginTransaction();

        try {
            $order->orderDetails()->create([
                'product_id' => $productId,
                'amount' => $amount,
                'amount_type_id' => $amountTypeId,
                'cost_price' => $product->cost_price,
                'sale_price' => $productSalePrice,
                'sum_cost_price' => $sumCostPrice,
                'sum_sale_price' => $sumSalePrice,
            ]);


            $order->increment('total_cost_price', $sumCostPrice);
            $order->increment('total_sale_price', $sumSalePrice);

            DB::commit();

            // Event
            OrderAddedNewProductEvent::dispatch($order, $product, [
                "price" => (float) $productSalePrice,
                "amount" => (float) $amount,
                "sum_sale_price" => (float) $sumSalePrice,
            ]);

            return [
                'message' => "#$order->id buyurtmaga `$product->name` nomli mahsulot muvaffaqiyatli qo'shildi"
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws InvalidDataException
     * @throws ServerErrorException
     */
    public function completed(array $data, int $id): array
    {
        // Req Body
        $itemsList = $data['items_list'];
        $comment = $data['comment'] ?? null;

        // Status
        $orderNewStatus = StatusService::findByCode('orderNew');
        $orderInProgressStatus = StatusService::findByCode('orderInProgress');

        // Order
        $order = Order::with('orderDetails')
            ->whereIn('status_id', [$orderNewStatus->id, $orderInProgressStatus->id])
            ->findOrFail($id);

        // Status Code
        $statusCompleted = StatusService::findByCode('orderCompleted');

        DB::beginTransaction();

        try {
            // Create New Completed Order
            $newCompletedOrder = CompletedOrder::create([
                'user_id' => Auth::id(),
                'order_id' => $order->id,
                'status_id' => $statusCompleted->id,
                'comment' => $comment,
                'total_cost_price' => 0,
                'total_sale_price' => 0,
                'customer_old_balance' => 0,
            ]);

            $totalCostPrice = 0;
            $totalSalePrice = 0;

            // Add Order Details completed amounts
            foreach ($itemsList as $item) {
                $itemId = $item['item_id'];
                $completedAmount = $item['completed_amount'];
                $productStockId = $item['product_stock_id'];
                $salePriceAmount = $item['sale_price_amount'] ?? null;

                // Order Detail Item
                $orderItem = OrderDetail::with('product')->findOrFail($itemId);
                $orderItemSalePrice = $orderItem->sale_price;
                $orderItemCostPrice = $orderItem->cost_price;

                // Product Stock
                $productStock = ProductStock::where('amount_type_id', $orderItem->amount_type_id)
                    ->findOrFail($productStockId);

                // Order Item Price
                $orderItemSalePrice = $orderItem->product->is_change_price && $salePriceAmount
                    ? $salePriceAmount
                    : $orderItemSalePrice;

                // Sum Price
                $sumCost = $completedAmount * $orderItemCostPrice;
                $sumPrice = $completedAmount * $orderItemSalePrice;

                // Calc Total Prices
                $totalCostPrice += $sumCost;
                $totalSalePrice += $sumPrice;

                // Decrement From Stock
                $productStock->decrement('amount', $completedAmount);

                // Update completed amount
                $orderItem->completed_amount = $completedAmount;
                $orderItem->cost_price = $orderItemCostPrice;
                $orderItem->sale_price = $orderItemSalePrice;
                $orderItem->sum_cost_price = $sumCost;
                $orderItem->sum_sale_price = $sumCost;
                $orderItem->save();
            }

            // Change Total Price Of Completed Order
            $newCompletedOrder->total_cost_price = $totalCostPrice;
            $newCompletedOrder->total_sale_price = $totalSalePrice;
            $newCompletedOrder->save();

            // Change Order Status
            $order->status_id = $statusCompleted->id;
            $order->save();

            DB::commit();

            // Event
            OrderCompletedEvent::dispatch($order);

            return [
                'message' => "Buyurtma topshirishga tayyor, hozirgi holati tayyorlandi",
                'data' => [
                    'status' => $statusCompleted
                ]
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws ServerErrorException
     */
    public function submit(array $data, int $id)
    {
        // Data
        $comment = $data['comment'] ?? null;

        $statusCompleted = StatusService::findByCode('orderCompleted');

        // Order
        $order = Order::with('orderDetails')
            ->where('status_id', $statusCompleted->id)
            ->findOrFail($id);

        // Status Code
        $statusSubmitted = StatusService::findByCode('orderSubmitted');

        // Customer
        $customer = Customer::findOrFail($order->customer_id);

        // Completed Order
        $completedOrder = CompletedOrder::where('order_id', $order->id)->firstOrFail();

        DB::beginTransaction();

        try {

            // Order
            $order->status_id = $statusSubmitted->id;
            $order->save();

            // Completed Order
            $completedOrder->submitted_comment = $comment;
            $completedOrder->status_id = $statusSubmitted->id;
            $completedOrder->customer_old_balance = $order->customer->balance;
            $completedOrder->save();

            // Customer
            $customer->decrement('balance', $completedOrder->total_sale_price);

            DB::commit();

            // Event
            OrderSubmittedEvent::dispatch($order);

            return [
                'message' => "Buyurtma muvaffaqiyatli topshirildi! Mijoz balansini tekshiring",
                'data' => [
                    'status' => $statusSubmitted
                ]
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function getTotalsByStatusCode(?string $statusCode): array
    {
        switch ($statusCode) {
            case 'orderCompleted':
            case 'orderSubmitted':
                return $this->getTotalsOrderCompleted($statusCode);
            default:
                return $this->getTotalsOrderOther($statusCode);
        }
    }

    private function getTotalsOrderCompleted(string $statusCode): array
    {
        $authUser = Auth::user();

        $status = StatusService::findByCode($statusCode);

        $query = DB::table('completed_orders')
            ->select(
                DB::raw('SUM(completed_orders.total_sale_price) as total_amount'),
                DB::raw('COUNT(completed_orders.id) as total_count')
            )
            ->whereBetween('completed_orders.updated_at', [$this->startDate, $this->endDate])
            ->where('completed_orders.status_id', $status->id);

        if (!$authUser->isAdmin()) {
            $query->where('user_id', $authUser->id);
        }

        $result = $query->first();

        return [
            'total_amount' => (float) $result->total_amount,
            'total_count' => $result->total_count,
        ];
    }

    private function getTotalsOrderOther(?string $statusCode): array
    {
        $authUser = Auth::user();

        $query = DB::table('orders')
            ->leftJoin('completed_orders', 'orders.id', '=', 'completed_orders.order_id')
            ->select(
                DB::raw('SUM(IF(completed_orders.order_id IS NULL, orders.total_sale_price, completed_orders.total_sale_price)) as total_amount'),
                DB::raw('COUNT(orders.id) as total_count')
            )
            ->whereBetween('orders.updated_at', [$this->startDate, $this->endDate]);

        // Status When Exist
        if ($statusCode) {
            $statusId = StatusService::findByCode($statusCode);
            $query->where('orders.status_id', $statusId->id);
        }

        if (!$authUser->isAdmin()) {
            $query->where('user_id', $authUser->id);
        }

        $result = $query->first();

        return [
            'total_amount' => (float) $result->total_amount,
            'total_count' => $result->total_count,
        ];
    }

    /**
     * Create and instantly deliver a POS order (for /pos-order endpoint).
     * Checks stock, decrements it, sets status to completed, and processes payment.
     *
     * @param array $data
     * @return array
     * @throws ServerErrorException
     */
    public function createInstantPosOrder(array $data): array
    {
        $authUser = Auth::user();
        $customerId = $data['customer_id'];
        $productList = $data['product_list'];
        $payment = $data['payment'];

        $submitOrderStatus = StatusService::findByCode('orderSubmitted');
        $customer = Customer::findOrFail($customerId);
        $productsId = array_column($productList ?? [], 'product_id');
        $products = Product::whereIn('id', $productsId)->get();
        $pluckIsChangePrice = $products->pluck('is_change_price', 'id');
        $pluckedCostPrice = $products->pluck('cost_price', 'id');
        $pluckedSalePrice = $products->pluck('sale_price', 'id');
        $userWallet = UserWallet::find($payment['user_wallet_id']);
        $orderCode = GenerateOrderCode::generate($customerId);
        $statusDebtCustomer = StatusService::findByCode('paymentCustomer');

        DB::beginTransaction();
        try {
            // Stock check and decrement
            $this->decrementStockForOrder($products, $productList, true);

            $order = Order::create([
                'user_id' => $authUser->id,
                'customer_id' => $customer->id,
                'status_id' => $submitOrderStatus->id,
                'total_cost_price' => 0,
                'total_sale_price' => 0,
                'ord_code' => $orderCode
            ]);

            $totalCostPrice = 0;
            $totalSalePrice = 0;
            $orderDetails = [];
            foreach ($productList as $item) {
                $productId = $item['product_id'];
                $amount = $item['amount'];
                $amountTypeId = $item['amount_type_id'];
                $salePriceAmount = $item['sale_price_amount'] ?? null;
                $costPrice = $pluckedCostPrice[$productId];
                $salePrice = $pluckIsChangePrice[$productId] && $salePriceAmount ? $salePriceAmount : $pluckedSalePrice[$productId];
                $sumCostPrice = $costPrice * $amount;
                $sumSalePrice = $salePrice * $amount;
                $orderDetails[] = [
                    'product_id' => $productId,
                    'amount' => $amount,
                    'amount_type_id' => $amountTypeId,
                    'cost_price' => $costPrice,
                    'sale_price' => $salePrice,
                    'sum_cost_price' => $sumCostPrice,
                    'sum_sale_price' => $sumSalePrice,
                ];
                $totalCostPrice += $sumCostPrice;
                $totalSalePrice += $sumSalePrice;
            }
            $order->orderDetails()->createMany($orderDetails);
            $order->update([
                'total_cost_price' => $totalCostPrice,
                'total_sale_price' => $totalSalePrice,
            ]);

            $paymentModel = new Payment([
                'user_id' => $authUser->id,
                'status_id' => $statusDebtCustomer->id,
            ]);
            $customer->payments()->save($paymentModel);
            $sumAmount = $payment['amount'] * $payment['currency_amount'];
            $paymentModel->wallets()->attach($userWallet->wallet_id, [
                'amount' => $payment['amount'],
                'rate_amount' => $payment['currency_amount'],
                'sum_price' => $sumAmount,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
            $userWallet->increment('amount', $payment['amount']);
            $customer->increment('balance', $sumAmount);

            // Create completed order record
            CompletedOrder::create([
                'user_id' => $authUser->id,
                'order_id' => $order->id,
                'status_id' => $submitOrderStatus->id,
                'total_cost_price' => $totalCostPrice,
                'total_sale_price' => $totalSalePrice,
                'customer_old_balance' => $customer->balance,
                'comment' => null,
                'submitted_comment' => null,
            ]);

            DB::commit();
            OrderCreatedEvent::dispatch($order);
            $formatVal = number_format($sumAmount);

            $formatOrderVal = number_format($totalSalePrice);

            return [
                'message' => "Yangi {$formatOrderVal} so'mlik buyurtma muvaffaqiyatli topshirildi! {$customer->first_name}.{$customer->last_name[0]} mijozdan {$formatVal} so'm o'tkazma qabul qilindi",
                'data' => [
                    'order_id' => $order->id,
                ]
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Decrement stock for each product in the order, or throw if not enough stock.
     *
     * @param \Illuminate\Support\Collection $products
     * @param array $productList
     * @param bool $throwOnInsufficient
     * @return void
     * @throws \Exception
     */
    private function decrementStockForOrder($products, array $productList, bool $throwOnInsufficient = false): void
    {
        foreach ($productList as $item) {
            $product = $products->find($item['product_id']);
            $stock = $product->stocks()->where('amount_type_id', $item['amount_type_id'])->first();
            if (!$stock || $stock->amount < $item['amount']) {
                if ($throwOnInsufficient) {
                    throw new \Exception("{$product->name} uchun yetarli mahsulot yo'q!");
                }
                continue;
            }
            $stock->decrement('amount', $item['amount']);
        }
    }
}
