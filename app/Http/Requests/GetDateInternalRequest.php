<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class GetDateInternalRequest extends FormRequest
{

    public function authorize(): bool
    {
        $authUser  = Auth::user();
        return $authUser->isAdmin();
    }


    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date|date_format:d-m-Y|before_or_equal:end_date',
            'end_date' => 'nullable|date|date_format:d-m-Y|after_or_equal:start_date',
        ];
    }
}
