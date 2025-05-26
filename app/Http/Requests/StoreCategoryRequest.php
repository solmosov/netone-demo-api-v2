<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCategoryRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::user()->isAdmin();
    }


    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:polkas,name',
            'parent_id' => 'nullable|integer|max:255|exists:polkas,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => "Nomi talab qilinadi",
            "name.unique" => "Bu nomli polka allaqachon mavjud",
            "parent_id.exists" => "Ota katalokni noto'g'ri tanladingiz",
        ];
    }
}
