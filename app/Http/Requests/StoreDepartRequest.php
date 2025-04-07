<?php

namespace App\Http\Requests;

use App\Models\Polka;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDepartRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'user_list' => 'required|array',
            'user_list.*' => 'required|numeric|exists:users,id',
            'polka_id' => 'required|numeric|exists:polkas,id',
            'name' => 'required|string|unique:departs,name',
            'comments' => 'nullable|string|min:6|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => "Bu bo'lim allaqachon mavjud",
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $polka_id = $this->input('polka_id');

            if (Polka::where('id', $polka_id)->whereNotNull('owner_id')->exists()) {
                $validator->errors()->add('polka_id', "Bu polkani tanlash mumkin emas");
            }
        });
    }
}
