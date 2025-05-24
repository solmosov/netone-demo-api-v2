<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateOrderAddProductRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'amount_type_id' => 'required|exists:amount_types,id',
            'amount' => 'required|numeric|min:0',
            'sale_price_amount' => 'nullable|numeric|min:0',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $productId = $this->input('product_id');
            $salePriceAmount = $this->input('sale_price_amount') ?? null;

            if ($salePriceAmount) {
                $product = Product::findOrFail($productId);
                if (!$product->is_change_price) {
                    $validator->errors()->add('product_id', "{$product->name} - mahsulotning narxini o'zgartirish mumkin emas!");
                }
            }
        });
    }
}
