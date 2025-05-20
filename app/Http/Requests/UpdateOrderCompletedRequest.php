<?php

namespace App\Http\Requests;

use App\Exceptions\InvalidDataException;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductStock;
use App\Services\Status\StatusService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateOrderCompletedRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items_list' => 'required|array',
            'items_list.*.item_id' => 'required|integer|exists:order_details,id',
            'items_list.*.product_stock_id' => 'required|integer|exists:product_stocks,id',
            'items_list.*.completed_amount' => 'required|numeric|min:0',
            'comment' => 'nullable|string|min:6|max:255',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $orderId = $this->route('id');
            $itemsList = $this->input('items_list');

            // Status
            $orderNewStatus = StatusService::findByCode('orderNew');
            $orderInProgressStatus = StatusService::findByCode('orderInProgress');

            // Order
            $order = Order::with('orderDetails')
                ->whereIn('status_id', [$orderNewStatus->id, $orderInProgressStatus->id])
                ->findOrFail($orderId);

            // Length
            $orderItemLength = $order->orderDetails->count();
            $prodItemLength = count($itemsList);

            if ($orderItemLength !== $prodItemLength) {
                throw new InvalidDataException("Siz buyurtma mahsulotlarni noto'g'ri kiritmoqdasiz, iltimos etiborli bo'ling.");
            }

            foreach ($itemsList as $key => $item) {
                $itemId = $item['item_id'];
                $productStockId = $item['product_stock_id'];
                $completedAmount = $item['completed_amount'];

                $orderItem = OrderDetail::findOrFail($itemId);

                $productStock = ProductStock::findOrFail($productStockId);

                if ($orderItem->product_id != $productStock->product_id) {
                    $validator->errors()->add("items_list.$key.product_stock_id", "Buyurtma mahsulot bilan zaxira polka mahsulot bir-biriga mos emas");
                } else if ($orderItem->amount_type_id != $productStock->amount_type_id) {
                    $validator->errors()->add("items_list.$key.product_stock_id", "Buyurtma mahsuloti bilan zaxira polka mahsulot o'lchov birligi mos emas");
                } else if ($productStock->amount < $completedAmount) {
                    $validator->errors()->add("items_list.$key.completed_amount", "Zaxira yetarli emas");
                }
            }
        });
    }
}
