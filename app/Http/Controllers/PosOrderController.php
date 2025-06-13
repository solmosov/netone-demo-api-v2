<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalePosOrderRequest;
use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class PosOrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    /**
     * Handle POS order creation: checks stock, creates order, and delivers instantly if possible.
     *
     * @param StoreSalePosOrderRequest $request
     * @return JsonResponse
     */
    public function store(StoreSalePosOrderRequest $request): JsonResponse
    {
        Gate::authorize('create', Order::class);
        $data = $request->validated();

        try {
            $result = $this->orderService->createInstantPosOrder($data);
            return response()->json($result,201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Check if all products have enough stock for the order.
     *
     * @param array $productList
     * @throws \Exception
     */
    private function checkStock(array $productList): void
    {
        foreach ($productList as $item) {
            $product = Product::findOrFail($item['product_id']);
            $stock = $product->stocks()->where('amount_type_id', $item['amount_type_id'])->first();
            if (!$stock || $stock->amount < $item['amount']) {
                throw new \Exception("{$product->name} uchun yetarli mahsulot yo'q!");
            }
        }
    }
} 