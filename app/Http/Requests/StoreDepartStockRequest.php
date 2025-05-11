<?php

namespace App\Http\Requests;

use App\Exceptions\InvalidDataException;
use App\Exceptions\NotFoundException;
use App\Models\Depart;
use App\Models\DepartStock;
use App\Models\Polka;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class StoreDepartStockRequest extends FormRequest
{

    public function authorize(): bool
    {
        $user = Auth::user();
        return $user->isAdmin() || $user->isProducer();
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

            // Exist Product Stock
            $exists = ProductStock::where('polka_id', $polkaId)
                ->exists();
            if (!$exists) {
                throw new InvalidDataException("Bu zaxira polkani tanlash mumkin emas. Boshqa zaxira polkani tanlang");
            }

            $polka = Polka::findOrFail($polkaId);
            if ($polka->owner_id !== null && $polka->owner_id !== $departId) {
                $validator->errors()->add("polka_id", "Bu polkani tanlash mumkin emas! Boshqa zaxira polkani tanlang");
            } else {
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
            }
        });
    }
}
