<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetChartByYearlyRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'year' => 'required|string|regex:/^\\d{4}$/',
        ];
    }
}
