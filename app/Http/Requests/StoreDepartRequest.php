<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'user_id' => 'nullable|numeric|exists:users,id',
            'name' => 'required|string|unique:departs,name',
            'comments' => 'nullable|string|min:6|max:255',
            'perm_list' => 'required|array',
            'perm_list.*.perm_id' => 'required|numeric|exists:trans_perms,id',
            'perm_list.*.depart_list' => 'required|array',
            'perm_list.*.depart_list.*' => 'required|numeric|exists:departs,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => "Bu bo'lim allaqachon mavjud",
            'perm_list.*.depart_list.*' => "Bo'lim noto'g'ri tanlandi"
        ];
    }
}
