<?php

namespace App\Http\Requests;

use App\Models\ProductStock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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
            'items_list.*.product_id' => 'required|integer|exists:products,id',
            'items_list.*.amount_type_id' => 'required|integer|exists:amount_types,id',
            'items_list.*.amount' => 'required|numeric|min:0.01',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $itemsList = $this->input('items_list');

            foreach ($itemsList as $key => $item) {
                $productId = $item['product_id'];
                $polkaId = $item['polka_id'];
                $amountTypeId = $item['amount_type_id'];
                $amount = $item['amount'];

                $stock = ProductStock::where('product_id', $productId)
                    ->where('polka_id', $polkaId)->first();
                if (!$stock) {
                    return $validator->errors()->add("items_list.$key.polka_id", "Zaxira polkasi topilmadi");
                }

                if ($stock->amount_type_id != $amountTypeId) {
                    return $validator->errors()->add("items_list.$key.amount_type_id", "O'lchov birligi mos emas");
                }

                if ($stock->amount < $amount) {
                    return $validator->errors()->add("items_list.$key.amount", "Zaxira yetarli emas");
                }
            }
        });
    }
}
