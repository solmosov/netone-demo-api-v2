<?php

namespace App\Http\Requests;

use App\Models\DepartTransPermProperty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDepartAssignPermRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'perm_id' => 'required|numeric|exists:trans_perms,id',
            'owner_depart_id' => 'required|numeric|exists:departs,id',
            'depart_list' => 'required|array',
            'depart_list.*' => 'required|numeric|exists:departs,id'
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $permId = $this->input('perm_id');
            $ownerDepartId = $this->input('owner_depart_id');

            $exist = DepartTransPermProperty::where('trans_perm_id', $permId)
                ->where('depart_id', $ownerDepartId)
                ->exists();
            if ($exist) {
                $validator->errors()->add('perm_id', "Bu bo'lim ruxsati allaqachon mavjud");
            }
        });
    }
}
