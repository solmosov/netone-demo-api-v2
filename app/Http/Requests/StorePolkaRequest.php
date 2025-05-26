<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePolkaRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name',
            'parent_id' => 'nullable|integer|max:255|exists:categories,id',
            'owner_id' => 'nullable|integer|exists:users,id'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => "Nomi talab qilinadi",
            "name.unique" => "Bu nomli polka allaqachon mavjud",
            "parent_id.exists" => "Polkani noto'g'ri tanladingiz",
        ];
    }
}
