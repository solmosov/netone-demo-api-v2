<?php

namespace App\Http\Requests;

use App\Models\DepartStock;
use App\Models\ProductStock;
use App\Models\RecipeItems;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProductionProcessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'depart_id' => [
                'required',
                'integer',
                Rule::exists('depart_user', 'depart_id')->where('user_id', Auth::id()),
            ],
            'production_recipe_id' => 'required|integer|exists:production_recipes,id',
            'items_list' => 'required|array',
            'items_list.*.recipe_item_id' => [
                'required',
                'integer',
                Rule::exists('recipe_items', 'id')->where('production_recipe_id')
            ],
            'items_list.*.depart_stock_id' => 'required|integer|exists:depart_stocks,id',
            'items_list.*.amount' => 'required|numeric|min:0.01',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $authUser = Auth::user();
            $productionRecipeId = $this->input('production_recipe_id');
            $itemsList = $this->input('items_list');

            foreach ($itemsList as $key => $item) {
                $recipeItemId = $item['recipe_item_id'];
                $departStockId = $item['depart_stock_id'];
                $amount = $item['amount'];

                // Recipe Item
                $recipeItem = RecipeItems::where('product_recipe_id', $productionRecipeId)->firstOrFail('recipe_item_id');

                $departStock = DepartStock::findOrFail($departStockId);
                $exists = DB::table('depart_user')
                    ->where('user_id', $authUser->id)
                    ->where('depart_id', $departStockId)
                    ->exists();
                if (!$exists) {
                    $validator->errors()->add("items_list.*.depart_stock_id", "Zaxira polka noto'g'ri tanlandi");
                } else if ($departStock->amount_type_id !== $recipeItem->amount_type_id) {
                    $validator->errors()->add('items_list.*.amount', "Rersept o'chov birligi bilan zaxira mahsulot o'lchov birligi mos emas");
                } else if ($departStock->amount < $amount) {
                    $validator->errors()->add('items_list.*.amount', "Zaxira miqdor yetarli emas");
                }
            }
        });
    }
}
