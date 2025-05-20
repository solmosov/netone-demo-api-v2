<?php

namespace App\Http\Requests;

use App\Models\Polka;
use Illuminate\Foundation\Http\FormRequest;
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
            'polka_list' => 'required|array',
            'polka_list.*' => 'required|numeric|exists:polkas,id',
            'name' => 'required|string|unique:departs,name',
            'status_name' => 'required|string|max:255|unique:statuses,name',
            'comment' => 'nullable|string|min:6|max:255',
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
            $polkaList = $this->input('polka_list');

            foreach ($polkaList as $polkaId) {

                $polka = Polka::where('id', $polkaId)->whereNull('owner_id')->first();

                if (!$polka) {
                    $validator->errors()->add('polka_id', "Bu polkani tanlash mumkin emas");
                }
            }
        });
    }
}
