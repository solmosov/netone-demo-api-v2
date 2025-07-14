<?php

namespace App\Http\Requests;

use App\Models\Polka;
use App\Models\ProductStock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProductStockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'polka_id' => 'nullable|numeric|exists:polkas,id',
            'min_amount' => 'nullable|numeric|min:0|lte:max_amount',
            'max_amount' => 'nullable|numeric|min:0|gte:min_amount',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $productStockId = $this->route('product_stock');
            $polka_id = $this->input('polka_id');

            $exists = Polka::whereNull('owner_id')->find($polka_id);

            if ($exists) {
                $validator->errors()->add('polka_id', "Bu `$exists->name` polka bo'limga tegishli");
            } else {
                $productStock = ProductStock::findOrFail($productStockId);

                $exists = ProductStock::where('product_id', $productStock->product_id)
                    ->where('polka_id', $polka_id)
                    ->exists();
                if ($exists) {
                    $validator->errors()->add('polka_id', 'Bu mahsulot va polka zaxirasi allaqachon mavjud. Boshqa polkani tanlang');
                }
            }
        });
    }
}
