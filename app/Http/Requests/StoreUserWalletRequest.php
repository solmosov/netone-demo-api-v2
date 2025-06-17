<?php

namespace App\Http\Requests;

use App\Models\UserWallet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class StoreUserWalletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $authUser = Auth::user();

        return $authUser->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'wallet_id' => 'required|integer|exists:wallets,id',
            'wallet_amount' => 'nullable|numeric|min:0',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $userId = $this->input('user_id');
            $walletId = $this->input('wallet_id');

            $userWallet = UserWallet::with(['user', 'wallet'])
                ->where('user_id', $userId)
                ->where('wallet_id', $walletId)
                ->first();
            if ($userWallet) {
                $validator->errors()->add('wallet_id', "{$userWallet->user->name} foydalnuvchi uchun `{$userWallet->wallet->name}` hisob allaqachon ochilgan!");
            }
        });
    }
}
