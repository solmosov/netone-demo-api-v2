<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartTransRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'from_depart_id' => 'required|numeric|exists:departs,id',
            'to_depart_id' => 'required|numeric|exists:departs,id',
            'product_list' => 'required|array',
            'product_list.*.product_id' => 'required|numeric|exists:products,id',
            'product_list.*.amount' => 'required|numeric|min:0',
            'product_list.*.amount_type_id' => 'required|numeric|exists:amount_types,id',
            'comment' => 'nullable|string|min:6|max:255'
        ];
    }
}
