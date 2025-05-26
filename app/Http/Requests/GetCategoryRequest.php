<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetCategoryRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'is_tree' => 'nullable|boolean'
        ];
    }
}
