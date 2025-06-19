<?php

namespace App\Http\Requests;

use App\Models\UserWallet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePaymentSupplierRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'supplier_id' => 'required|numeric|exists:suppliers,id',
            'wallet_list' => 'required|array|min:1',
            'wallet_list.*.wallet_id' => [
                'required',
                'numeric',
                'exists:user_wallet,wallet_id,user_id,' . auth()->id()
            ],
            'wallet_list.*.amount' => 'required|numeric|min:0.01',
            'wallet_list.*.rate_amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string|min:6|max:255',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $walletList = $this->input('wallet_list');

            foreach ($walletList as $key => $item) {
                $walletId = $item['wallet_id'];
                $amount = $item['amount'];

                $userWallet = UserWallet::with('wallet')->findOrFail($walletId);

                if ($userWallet->wallet->amount < $amount) {
                    $walletName = $userWallet->wallet->name;
                    $validator->errors()->add("wallet_list.$key.wallet_id", "`$walletName` bu hisobingizda mablag' yetarli emas! Hisobingizni tekshiring");
                }
            }
        });
    }
}
