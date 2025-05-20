<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'user_list' => 'nullable|array',
            'user_list.*' => 'required|numeric|exists:users,id',
            'polka_id' => 'nullable|numeric|exists:polkas,id',
            'name' => 'nullable|string|unique:departs,name',
            'comment' => 'nullable|string|min:6|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => "Bu bo'lim allaqachon mavjud"
        ];
    }
}
