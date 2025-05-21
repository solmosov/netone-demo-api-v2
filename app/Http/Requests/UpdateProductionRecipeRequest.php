<?php

namespace App\Http\Requests;

use App\Models\RecipeItems;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class UpdateProductionRecipeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $recipeId = $this->route('production_recipe');

        return [
            'name' => 'required|string|max:255|unique:production_recipes,name',
            'out_product_id' => 'required|integer|exists:products,id',
            'out_amount_type_id' => 'required|integer|exists:amount_types,id',
            'out_amount' => 'required|numeric|min:0.01',
            'items_list' => 'required|array',
            'items_list.*.item_id' => 'nullable|integer|exists:recipe_items,id',
            'items_list.*.product_id' => 'required|integer|exists:products,id',
            'items_list.*.amount' => 'required|numeric|min:0.01',
            'items_list.*.amount_type_id' => 'required|integer|exists:amount_types,id',
            'items_list.*.is_change' => 'nullable|boolean',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $recipeId = $this->route('recipe');
            $itemsList = $this->input('items_list');


            foreach ($itemsList as $key => $item) {
                // Req Values
                $itemId = $item['item_id'] ?? null;

                if ($itemId !== null) {
                    $recipeItem = RecipeItems::where('production_recipe_id', $recipeId)->find($itemId);
                    if (!$recipeItem) {
                        $validator->errors()->add("items_list.$key.item_id", "Retsept mahsulotlaridan biri noto'g'ri tanlandi");
                    }
                }
            }
        });
    }
}
