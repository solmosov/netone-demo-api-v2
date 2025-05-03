<?php

namespace App\Http\Requests;

use App\Models\DepartStock;
use App\Models\DepartTransPermProperty;
use App\Models\DepartTransPermPropertyDetails;
use App\Models\Polka;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class StoreDepartTransProcessRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'trans_perm_id' => 'required|integer|exists:trans_perms,id',
            'sender_depart_id' => 'required|integer|exists:departs,id',
            'receiver_depart_id' => 'required|integer|exists:departs,id',
            "product_list" => "required|array",
            "product_list.*.depart_stock_id" => "required|integer|exists:depart_stocks,id",
            "product_list.*.amount" => "required|numeric|min:0",
            'comment' => 'nullable|min:6|max:255',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $transPermId = $this->input('trans_perm_id');
        $fromDepartId = $this->input('sender_depart_id');
        $toDepartId = $this->input('receiver_depart_id');
        $productList = $this->input('product_list');

        $departUser = DB::table('depart_user')
            ->where('user_id', Auth::id())
            ->where('depart_id', $fromDepartId)
            ->exists();
        if (!$departUser) {
            $validator->errors()->add('sender_depart_id', "O'zingizga tegishli bo'limni tanlang");
        } else {
            $departTransPermProperty = DepartTransPermProperty::where('trans_perm_id', $transPermId)
                ->where('depart_id', $fromDepartId)
                ->first();

            if (!$departTransPermProperty) {
                $validator->errors()->add('sender_depart_id', "Bo'lim ruxsat topilmadi");
            } else {

                $departPermPropertyDetail = DepartTransPermPropertyDetails::with('depart')->where('property_id', $departTransPermProperty->id)
                    ->where('depart_id', $toDepartId)
                    ->first();

                if (!$departPermPropertyDetail) {
                    $validator->errors()->add('receiver_depart_id', "Bu {$departPermPropertyDetail->depart->name} bo'lim uchun sizda ruxsat mavjud emas!");
                } else {
                    // Product List validation
                    foreach ($productList as $key => $item) {
                        $departStockId = $item['depart_stock_id'];
                        $amount = $item['amount'];

                        $stock = DepartStock::find($departStockId);
                        if (!$stock) {
                            $validator->errors()->add("product_list.$key.polka_id", "Sizda bu zaxira mavjud emas!");
                        } else if ($stock->amount < $amount) {
                            $validator->errors()->add("product_list.$key.amount", "Zaxira polkasida zaxira yetarli emas!");
                        }
                    }
                }
            }
        }
    }
}
