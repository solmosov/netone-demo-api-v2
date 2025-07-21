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
            'product_category_id' => 'nullable|integer|exists:categories,id',
            'product_id' => 'nullable|integer|exists:products,id',
            'customer_id' => 'nullable|integer|exists:customers,id',
            'seller_id' => 'nullable|integer|exists:users,id',
        ];
    }
}
