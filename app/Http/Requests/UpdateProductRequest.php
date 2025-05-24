<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProductRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            // 'name' => 'nullable|string|max:255',
            'name' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'name')->ignore($productId),
            ],
            'sale_price' => 'nullable|numeric|min:0',
            'price_amount_type_id' => 'nullable|integer|exists:amount_types,id',
            'is_change_price' => 'nullable|boolean'
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Bu nomli mahsulot allaqachon mavjud'
        ];
    }
}
