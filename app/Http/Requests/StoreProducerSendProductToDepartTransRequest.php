<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProducerSendProductToDepartTransRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'depart_id' => 'required|integer|exists:departs,id',
            'items_list' => 'required|array',
            'items_list.*.product_stock_id' => 'required|integer|exists:product_stocks,id',
            'items_list.*.amount' => 'required|numeric|min:0',
            'comment' => 'nullable|string|min:6|max:255',
        ];
    }
}
