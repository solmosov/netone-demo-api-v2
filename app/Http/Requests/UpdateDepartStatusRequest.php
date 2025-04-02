<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartStatusRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'depart_id' => 'nullable|numeric|exists:departs,id',
            // 'name' => 'nullable|string|unique:depart_statuses',
            'name'  => [
                'nullable',
                'string',
                Rule::unique('depart_statuses', 'name')->ignore($this->depart_status),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => "Bo'lim status allaqachon mavjud",
        ];
    }
}
