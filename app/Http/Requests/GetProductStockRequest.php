<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetProductStockRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'is_min' => 'nullable|boolean',
            'is_max' => 'nullable|boolean'
        ];
    }
}
