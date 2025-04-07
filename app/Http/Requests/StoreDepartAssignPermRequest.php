<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
}
