<?php

namespace App\Http\Requests;

use App\Exceptions\NotFoundException;
use App\Models\DepartStock;
use App\Models\DepartTransPermProperty;
use App\Models\DepartTransPermPropertyDetails;
use App\Services\Department\DepartTransPermService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class StoreDepartTransProcessRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::user()->isDepartUser();
    }


    public function rules(): array
    {
        return [
            'trans_perm_id' => 'required|exists:trans_perms,id',
            'sender_depart_id' => 'required|exists:departs,id',
            'receiver_depart_id' => 'required|exists:departs,id',
            'product_list' => 'required|array',
            'product_list.*.depart_stock_id' => 'required|exists:depart_stocks,id',
            'product_list.*.amount' => 'required|numeric|min:0',
            'comment' => 'nullable|string|min:6,max:255',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $authId = Auth::id();
            $senderDepartId = $this->input('sender_depart_id');
            $receiverDepartId = $this->input('receiver_depart_id');
            $productList = $this->input('product_list');

            $exists = DB::table('depart_user')
                ->where('depart_id', $senderDepartId)
                ->where('user_id', $authId)
                ->exists();

            if (!$exists) {
                $validator->errors()->add('sender_depart_id', "O'zingizga tegishli bo'lgan bo'limni tanlang");
            } else {
                // Receive Trans Perm
                $sendTransPerm = DepartTransPermService::getSendProduct();
                
                // Check Access
                $departTransPerm = DepartTransPermProperty::where('trans_perm_id', $sendTransPerm->id)
                    ->where('depart_id', $senderDepartId)
                    ->first();
                if(!$departTransPerm){
                    throw new NotFoundException("Sizda bu bo'limga mahsulot yuborish uchun ruxsat mavjud emas!");
                }

                $exists = DepartTransPermPropertyDetails::where('property_id', $departTransPerm->id)
                    ->where('depart_id', $receiverDepartId)
                    ->exists();
                if (!$exists) {
                    $validator->errors()->add('receiver_depart_id', "Bu bo'limga mahsulot o'tkazish uchun sizda ruxsat mavjud emas!");
                } else {
                    foreach ($productList as $key => $item) {
                        $departStockId = $item['depart_stock_id'];
                        $amount = $item['amount'];

                        $departStock = DepartStock::where('depart_id', $senderDepartId)
                            ->find($departStockId);

                        if (!$departStock) {
                            $validator->errors()->add("product_list.$key.depart_stock_id", "Bo'lim zaxira topilmadi");
                        } else if ($departStock->amount < $amount) {
                            $validator->errors()->add("product_list.$key.depart_stock_id", "Bo'lim zaxira miqdori yetarli emas");
                        }
                    }
                }
            }
        });
    }
}
