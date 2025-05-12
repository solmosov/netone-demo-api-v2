<?php

namespace App\Http\Requests;

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
            'trans_list' => 'required|array',
            'trans_list.*.item_id' => 'required|integer|depart_trans_process_details,id',
            'trans_list.*.product_stock_id' => 'required|integer|product_stocks,id',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $transId = $this->route('producer_product_trans');
            $transList = $this->input('trans_list');

            foreach ($transList as $key => $item) {
                $itemId = $item['item_id'];
                $productStockId = $item['product_stock_id'];

                // Transfer Item
                $transferItem = ProducerProductTransDetails::where('trans_id', $transId)->find($itemId);
                if (!$transferItem) {
                    $validator->errors()->add("trans_list.$key.item_id", "O'tkazma mahsulot topilmadi");
                } else {
                    $productStock = ProductStock::findOrFail($productStockId);
                    if ($productStock->amount_type_id != $transferItem->amount_type_id) {
                        $validator->errors()->add("trans_list.$key.product_stock_id", "O'tkazma mahsulot o'lchov birligi bilan zaxira mahsulot o'lchov birligi mos emas!");
                    }
                }
            }
        });
    }
}
