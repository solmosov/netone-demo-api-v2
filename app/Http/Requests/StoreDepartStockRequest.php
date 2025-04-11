<?php

namespace App\Http\Requests;

use App\Models\Depart;
use App\Models\DepartStock;
use App\Models\Polka;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDepartStockRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'depart_id' => 'required|integer|exists:departs,id',
            'polka_id' => 'required|integer|exists:polkas,id',
            'product_id' => 'required|integer|exists:products,id'
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $departId = $this->input('depart_id');
            $polkaId = $this->input('polka_id');
            $productId = $this->input('product_id');

            $exists = DepartStock::where('depart_id', $departId)
                ->where('polka_id', $polkaId)
                ->where('product_id', $productId)
                ->exists();

            if ($exists) {
                $depart = Depart::findOrFail($departId);
                $polka = Polka::findOrFail($polkaId);
                $product = Product::findOrFail($productId);

                $validator->errors()->add('depart_id', "{$depart->name} bo'lim uchun {$polka->name} va {$product->name} allaqachon zaxira sifatida yaratilgan");
            }
        });
    }
}
