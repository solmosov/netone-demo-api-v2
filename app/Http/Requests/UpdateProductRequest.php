<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProductRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'sale_price' => 'nullable|numeric|min:0',
            'price_amount_type_id' => 'nullable|integer|exists:amount_types,id',
            'is_change_price' => 'nullable|boolean'
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $productId = $this->route('product');
            $name = $this->input('name');

            $exists = Product::where('id', '<>', $productId)
                ->where('name', $name);
            if ($exists) {
                $validator->errors()->add('name', "Bu nomli mahsulot allaqachon mavjud");
            }
        });
    }
}
