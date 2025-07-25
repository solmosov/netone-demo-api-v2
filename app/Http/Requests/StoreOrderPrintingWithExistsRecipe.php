<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreOrderPrintingWithExistsRecipe extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'recipe_id' => 'required|integer|exists:production_recipes,id',
            'out_product_id' => 'required|integer|exists:products,id',
            'out_amount_type_id' => 'required|integer|exists:amount_types,id',
            'out_amount' => 'required|numeric|min:0.01',
            'items_list' => 'required|array',
            'items_list.*.recipe_item_id' => 'required|integer|exists:products,id',
            'items_list.*.depart_id' => 'required|integer|exists:departs,id',
            'items_list.*.sale_price_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:255'
        ];
    }
}
