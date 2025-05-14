<?php

namespace App\Http\Requests;

use App\Exceptions\NotFoundException;
use App\Models\DepartStock;
use App\Models\ProducerProductTrans;
use App\Models\ProducerProductTransDetails;
use App\Services\Status\StatusService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class StoreProducerReceiveProductFromProducerRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::user()->isDepartUser();
    }


    public function rules(): array
    {
        return [
            'items_list' => 'required|array',
            'items_list.*.item_id' => 'required|integer|exists:producer_product_trans_details,id',
            'items_list.*.depart_stock_id' => 'required|integer|exists:depart_stocks,id'
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $authUser = Auth::user();
            $transId = $this->route('producer_product_trans');
            $itemsList = $this->input('items_list');

            // Status Send Product
            $statusSendProduct = StatusService::findByCode('producerSendProduct');

            // Transfer
            $transfer = ProducerProductTrans::where('status_id', $statusSendProduct->id)->findOrFail($transId);

            $departUser = DB::table('depart_user')
                ->where('user_id', $authUser->id)
                ->where('depart_id', $transfer->depart_id)
                ->first();
            if (!$departUser) {
                throw new NotFoundException("Qabul qilinadigan mahsulot o'tkazmasi topilmadi");
            }

            foreach ($itemsList as $key => $item) {
                $itemId = $item['item_id'];
                $departStockId = $item['depart_stock_id'];

                // Transfer Item
                $transferItem = ProducerProductTransDetails::with(['productStock'])
                    ->where('trans_id', $transId)
                    ->findOrFail($itemId);

                // Depart Stock
                $departStock = DepartStock::where('depart_id', $departUser->depart_id)->findOrFail($departStockId);

                if ($departStock->product_id != $transferItem->productStock->product_id) {
                    $validator->errors()->add("items_list.$key.depart_stock_id",  "Qabul qilinayotgan mahsulot bilan zaxira polka mahsuloti bir-biriga mos emas");
                } else if ($transferItem->amount_type_id != $departStock->amount_type_id) {
                    $validator->errors()->add("items_list.$key.depart_stock_id", "Yuborilayotgan mahsulot bilan qabul qilinayotgan zaxira mahsulot o'lchov birligi mos emas");
                } else if ($transferItem->productStock->amount < $transferItem->amount) {
                    $validator->errors()->add("items_list.$key.depart_stock_id", "Mahsulot yuboruvchining zaxirasi yetarli emas");
                }
            }
        });
    }
}
