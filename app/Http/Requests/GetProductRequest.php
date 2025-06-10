<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetProductRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'search' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:categories,id',
        ];
    }
}
