<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesPrintingOrderRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'name' => 'required|string|max:255|unique:production_recipes,name',
            'out_product_id' => 'required|integer|exists:products,id',
            'out_amount_type_id' => 'required|integer|exists:amount_types,id',
            'out_amount' => 'required|numeric|min:0.01',
            'items_list' => 'required|array',
            'items_list.*.product_id' => 'required|integer|exists:products,id',
            'items_list.*.amount' => 'required|numeric|min:0',
            'items_list.*.amount_type_id' => 'required|integer|exists:amount_types,id',
            'items_list.*.is_change' => 'nullable|boolean',
            'items_list.*.depart_id' => 'required|integer|exists:departs,id',
            'description' => 'nullable|string|max:255'
        ];
    }
}
