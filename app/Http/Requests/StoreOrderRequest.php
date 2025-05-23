<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
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
            'customer_id' => 'required|exists:customers,id',
            'product_list' => 'required|array',
            'product_list.*.product_id' => 'required|exists:products,id',
            'product_list.*.amount_type_id' => 'required|exists:amount_types,id',
            'product_list.*.amount' => 'required|numeric|min:0',
            'product_list.*.sale_price_amount' => 'nullable|numeric|min:0',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $productList = $this->input('product_list');

            foreach ($productList as $item) {
                $productId = $item['product_id'];
                $salePriceAmount = $item['sale_price_amount'] ?? null;

                if ($salePriceAmount) {
                    $product = Product::findOrFail($productId);
                    if (!$product) {
                        $validator->errors()->add('product_id', "{$product->name} - mahsulotning narxini o'zgartirish mumkin emas!");
                    }
                }
            }
        });
    }
}
