<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSalePosOrderRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        $authUser = Auth::user();

        return [
            'customer_id' => 'required|exists:customers,id',
            'product_list' => 'required|array',
            'product_list.*.product_id' => 'required|exists:products,id',
            'product_list.*.amount_type_id' => 'required|exists:amount_types,id',
            'product_list.*.amount' => 'required|numeric|min:0',
            'product_list.*.sale_price_amount' => 'nullable|numeric|min:0',
            'payment' => 'required|array',
            'payment.user_wallet_id' => [
                'required',
                'integer',
                Rule::exists('user_wallet', 'id')->where('user_id', $authUser->id)
            ],
            'payment.amount' => 'required|numeric|min:0',
            'payment.currency_amount' => 'required|numeric|min:0.01',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $productList = $this->input('product_list');

            foreach ($productList as $key => $item) {
                $productId = $item['product_id'];
                $salePriceAmount = $item['sale_price_amount'] ?? null;

                if ($salePriceAmount) {
                    $product = Product::findOrFail($productId);
                    if (!$product->is_change_price) {
                        $validator->errors()->add("product_list.$key.sale_price_amount", "{$product->name} - mahsulotning narxini o'zgartirish mumkin emas!");
                    }
                }
            }
        });
    }
}
