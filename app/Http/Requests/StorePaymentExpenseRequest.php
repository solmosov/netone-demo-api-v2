<?php

namespace App\Http\Requests;

use App\Models\UserWallet;
use App\Models\Wallet;
use App\Rules\WalletBelongsToUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class StorePaymentExpenseRequest extends FormRequest
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
            'expense_id' => 'required|exists:expenses,id',
            'user_wallet_id' => [
                'required',
                'integer',
                'exists:user_wallet,id,user_id,' . Auth::id(),
            ],
            'amount' => 'required|numeric|min:0',
            'rate_amount' => 'required|numeric|min:1',
            'comment' => 'nullable|string|min:3|max:255',
            'payment_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        $userName = Auth::user()->name;

        return [
            'wallet_id.exists' => "Xurmatli $userName foydalanuvchi, sizda mavjud bo'lmagan hisobni tanlayapsiz!"
        ];
    }

    public function withValidator(Validator $validator){
        $validator->after(function ($validator) {
            $userWalletId = $this->input('user_wallet_id');
            $amount = $this->input('amount');
            
            $userWallet = UserWallet::with('wallet')->findOrFail($userWalletId);

            if($userWallet->wallet->amount < $amount){
                $validator->errors()->add('amount', "{$userWallet->wallet->name} - hisobingizda mablag‘ yetarli emas! Hisobingizni tekshiring");
            }
        });
    }
}
