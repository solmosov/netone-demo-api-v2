<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderPrintingOrderRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'recipe_name' => 'required|string|max:255|unique:production_recipes,name',
            'recipe_out_product_id' => 'required|integer|exists:products,id',
            'recipe_out_amount_type_id' => 'required|integer|exists:amount_types,id',
            'recipe_out_amount' => 'required|numeric|min:0.01',
            'plan_amount' => 'required|number|min:0.01',
            'plan_amount_type_id' => 'required|integer|exists:amount_types,id',
            'items_list' => 'required|array',
            'items_list.*.product_id' => 'required|integer|exists:products,id',
            'items_list.*.amount' => 'required|numeric|min:0',
            'items_list.*.amount_type_id' => 'required|integer|exists:amount_types,id',
            'items_list.*.is_change' => 'nullable|boolean',
            'items_list.*.depart_id' => 'required|integer|exists:departs,id',
            'items_list.*.sale_price_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:255'
        ];
    }
}
