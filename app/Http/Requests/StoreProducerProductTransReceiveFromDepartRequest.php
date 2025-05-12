<?php

namespace App\Http\Requests;

use App\Models\ProducerProductTrans;
use App\Models\ProducerProductTransDetails;
use App\Models\ProductStock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class StoreProducerProductTransReceiveFromDepartRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::user()->isProducer();
    }


    public function rules(): array
    {
        return [
            'items_list' => 'required|array',
            'items_list.*.item_id' => 'required|integer|exists:producer_product_trans_details,id',
            'items_list.*.product_stock_id' => 'required|integer|exists:product_stocks,id',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            // Auth User
            $authUser = Auth::user();

            // Req
            $transId = $this->route('producer_product_trans');
            $transList = $this->input('items_list');

            // Transfer
            ProducerProductTrans::where('producer_id', $authUser->id)
                ->where('is_sending_depart', false)
                ->findOrFail($transId);

            foreach ($transList as $key => $item) {
                $itemId = $item['item_id'];
                $productStockId = $item['product_stock_id'];

                // Transfer Item
                $transferItem = ProducerProductTransDetails::with('departStock')
                    ->where('trans_id', $transId)
                    ->find($itemId);

                if (!$transferItem) {
                    $validator->errors()->add("items_list.$key.item_id", "O'tkazma mahsulot topilmadi");
                } else {
                    $productStock = ProductStock::findOrFail($productStockId);
                    if ($productStock->product_id != $transferItem->departStock->product_id) {
                        $validator->errors()->add("items_list.$key.product_stock_id", "O'tkazma mahsulot bilan qabul qilinayotgan zaxira mahsulot bir-biriga mos emas!");
                    } else if ($productStock->amount_type_id != $transferItem->amount_type_id) {
                        $validator->errors()->add("items_list.$key.product_stock_id", "O'tkazma mahsulot o'lchov birligi bilan zaxira mahsulot o'lchov birligi mos emas!");
                    }
                }
            }
        });
    }
}
