<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetOrderRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        $allowedStatuses = [
            'orderNew',
            'orderInProgress',
            'orderCancel',
            'orderCompleted',
            'orderSubmitted',
        ];

        return [
            'status' => ['nullable', 'string', 'in:' . implode(',', $allowedStatuses)],
            'start_date' => 'required|date|date_format:d-m-Y|before_or_equal:endDate',
            'end_date' => 'required|date|date_format:d-m-Y|after_or_equal:startDate',
        ];
    }
}
