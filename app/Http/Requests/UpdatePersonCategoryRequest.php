<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonCategoryRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name' => [
                'nullable',
                'string',
                'min:3',
                'max:255',
                Rule::unique('person_categories', 'name')->ignore($this->route('person_category'))
            ]
        ];
    }
}
