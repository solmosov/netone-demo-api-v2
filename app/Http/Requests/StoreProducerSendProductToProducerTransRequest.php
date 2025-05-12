<?php

namespace App\Http\Requests;

use App\Models\DepartStock;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class StoreProducerSendProductToProducerTransRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::user()->isDepartUser();
    }


    public function rules(): array
    {
        return [
            'depart_id' => 'required|integer|exists:departs,id',
            'producer_id' => 'required|integer|exists:users,id',
            'items_list' => 'required|array',
            'items_list.*.depart_stock_id' => 'required|integer|exists:depart_stocks,id',
            'items_list.*.amount' => 'required|numeric|min:0',
            'comment' => 'nullable|string|min:6|max:255'
        ];
    }

    public function withValidator(Validator $validator)
    {

        $validator->after(function ($validator) {
            // Auth User
            $authUser = Auth::user();

            // Inputs
            $departId = $this->input('depart_id');
            $producerId = $this->input('producer_id');
            $itemsList = $this->input('items_list');

            $exists = DB::table('depart_user')
                ->where('depart_id', $departId)
                ->where('user_id', $authUser->id)
                ->exists();

            if (!$exists) {
                $validator->errors()->add("depart_id", "O'zingizga tegishli bo'limni tanlang");
            }

            $exists = Role::where('name', 'producer')->whereHas('users', function ($q) use ($producerId) {
                $q->where('id', $producerId);
            })
                ->exists();
            if (!$exists) {
                $validator->errors()->add('producer_id', "Producer topilmadi");
            } else {
                foreach ($itemsList as $key => $item) {
                    $departStockId = $item['depart_stock_id'];
                    $amount = $item['amount'];

                    // Department Stock
                    $departStock = DepartStock::where('depart_id', $departId)->findOrFail($departStockId);

                    if ($departStock->amount < $amount) {
                        $validator->errors()->add("items_list.$key.depart_stock_id", "Zaxira yetarli emas");
                    }
                }
            }
        });
    }
}
