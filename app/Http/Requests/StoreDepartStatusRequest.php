<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartStatusRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'depart_id' => 'required|numeric|exists:departs,id',
            'name' => 'required|string|unique:depart_statuses',
        ];
    }

    public function messages():array
    {
        return [
            'name.required' => "Bu bo'lim status allaqachon mavjud",
        ];
    }
}
