<?php

namespace App\Http\Requests;

use App\Models\DepartTransPermProperty;
use App\Models\ProductStock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDepartPermissionRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'owner_depart_id' => 'required|numeric|exists:departs,id',
            'perm_id' => 'required|numeric|exists:trans_perms,id',
            'depart_list' => 'required|array',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $departId = $this->input('owner_depart_id');
            $permId = $this->input('perm_id');


            if (DepartTransPermProperty::where('depart_id', $departId)
                ->where('trans_perm_id', $permId)
                ->exists()
            ) {
                $validator->errors()->add('owner_depart_id', 'Bu ruxsat allaqachon mavjud');
            }
        });
    }
}
