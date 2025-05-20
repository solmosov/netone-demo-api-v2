<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProductionRecipeItemRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::user()->isAdmin();
    }


    public function rules(): array
    {
        return [
            'product_id' => 'nullable|integer|exists:products,id',
            'amount' => 'nullable|numeric|min:0.01',
            'amount_type_id' => 'nullable|integer|exists:amount_types,id',
            'is_change' => 'nullable|boolean',
        ];
    }
}
