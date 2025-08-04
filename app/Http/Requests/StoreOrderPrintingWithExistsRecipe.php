<?php

namespace App\Http\Requests;

use App\Models\ProductionRecipe;
use App\Models\RecipeItems;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'amount' => 'required|numeric|min:0',
            'amount_type_id'  => 'required|integer|exists:amount_types,id',
            'sale_price_amount' => 'nullable|numeric|min:0',
            'items_list' => 'required|array',
            'items_list.*.recipe_item_id' => 'required|integer|exists:recipe_items,id',
            'items_list.*.depart_id' => 'required|integer|exists:departs,id',
            'description' => 'nullable|string|max:255'
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $recipeId = $this->input('recipe_id');
            $sale_price_amount = $this->input('sale_price_amount') ?? null;
            $items_list = $this->input('items_list');

            $recipe = ProductionRecipe::with('outProduct')->findOrFail($recipeId);
            if ($sale_price_amount && !$recipe->outProduct->is_change_price) {
                $validator->errors()->add('sale_price_amount', "Sotuv narxini o'zgartirish mumkin emas!");
            }

            foreach ($items_list as $index => $item) {
                $exists = RecipeItems::where('production_recipe_id', $recipeId)->find($item['recipe_item_id']);
                if (!$exists) {
                    $validator->errors()->add("items_list.{$index}.recipe_item_id", "Retsept mahsuloti noto'g'ri tanlandi");
                }
            }
        });
    }
}
