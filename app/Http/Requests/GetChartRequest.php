<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetChartRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'date_type' => 'required|string|in:yearly,monthly,weekly',
        ];
    }
}
