<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetCustomerRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'person_category_id' => 'nullable|integer|exists:person_categories,id',
            'start_balance' => 'nullable|numeric',
            'end_balance' => 'nullable|numeric',
        ];
    }
}
