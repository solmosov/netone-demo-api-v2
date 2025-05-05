<?php

namespace App\Http\Requests;

use App\Models\Department\DepartTransProcessDetail;
use App\Models\DepartStock;
use App\Models\DepartTransProcess;
use App\Models\ProcessItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class StoreDepartTransProcessReceiveRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'details_list' => 'required|array',
            'details_list.*.item_id' => 'required|integer|exists:depart_trans_process_details,id',
            'details_list.*.depart_stock_id' => 'required|integer|exists:depart_stocks,id'
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $authId = Auth::id();
            $transId = $this->route('trans_process_id');
            $detailsList = $this->input('details_list');

            // Trans Process
            $transProcess = DepartTransProcess::with('details')->findOrFail($transId);

            $exists = DB::table('depart_user')
                ->where('depart_id', $transProcess->receiver_depart_id)
                ->where('user_id', $authId)
                ->exists();

            if (!$exists) {
                $validator->errors()->add("trans_process_id", "O'tkazma noto'g'ri tanlandi");
            } else {
                if (count($detailsList) < $transProcess->details->count()) {
                    $validator->errors()->add("details_list", "Yuborilgan mahsulotlar to'liq emas!");
                } else {
                    foreach ($detailsList as $key => $item) {
                        $itemId = $item['item_id'];
                        $departStockId = $item['depart_stock_id'];

                        // Process Item
                        $processItem = DepartTransProcessDetail::findOrFail($itemId);

                        // Department Stock
                        $departStock = DepartStock::where('id', $departStockId)
                            ->where('depart_id', $transProcess->receiver_depart_id)
                            ->first();
                        if (!$departStock) {
                            $validator->errors()->add("details_list_{$key}_depart_stock_id", "Bo'lim zaxira polkasini to'g'ri tanlang");
                        } else if ($departStock->amount_type_id !== $processItem->amount_type_id) {
                            $validator->errors()->add("details_list_{$key}_depart_stock_id", "Yuborilgan mahsulot bilan qabul qilinadigan zaxira polkangiz o'lchov birligi mos emas");
                        }
                    }
                }
            }
        });
    }
}
