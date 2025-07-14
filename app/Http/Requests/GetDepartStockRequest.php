<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetDepartStockRequest extends FormRequest
{

    public function authorize(): bool
    {
        return false;
    }


    public function rules(): array
    {
        return [
            'depart_id' => 'nullable|integer|exists:departs,id',
            'is_min' => 'nullable|boolean',
            'is_max' => 'nullable|boolean'
        ];
    }
}
