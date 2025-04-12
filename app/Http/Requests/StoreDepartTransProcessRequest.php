<?php

namespace App\Http\Requests;

use App\Models\DepartTransPermProperty;
use App\Models\DepartTransPermPropertyDetails;
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
            "product_list"  => "required|array",
            "product_list.*.product_id" => "required|integer|exists:products,id",
            "product_list.*.polka_id" => "required|integer|exists:polkas,id",
            "product_list.*.amount" => "required|numeric|min:0",
            "product_list.*.amount_type_id" => "required|integer|exists:polkas,id",
            'comment' => 'nullable|min:6|max:255',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $transPermId = $this->input('trans_perm_id');
        $fromDepartId = $this->input('sender_depart_id');
        $toDepartId = $this->input('receiver_depart_id');

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

            if (!$departTransPermProperty->exists()) {
                $validator->errors()->add('sender_depart_id', "Bo'lim ruxsat topilmadi");
            } else {

                $departPermPropertyDetail = DepartTransPermPropertyDetails::with('depart')->where('property_id', $departTransPermProperty->id)
                    ->where('depart_id', $toDepartId)
                    ->first();

                if ($departPermPropertyDetail->exists()) {
                    $validator->errors()->add('receiver_depart_id', "Bu {$departPermPropertyDetail->depart->name} bo'lim uchun sizda ruxsat mavjud emas!");
                }
            }
        }
    }
}
