<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StorePaymentCustomerRequest extends FormRequest
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
        $authUser = Auth::user();

        return [
            'customer_id' => 'required|numeric|exists:customers,id',
            'wallet_list' => 'required|array|min:1',
            'wallet_list.*.user_wallet_id' => [
                'required',
                'numeric',
                "exists:user_wallet,id,user_id,{$authUser->id}"
            ],
            'wallet_list.*.amount' => 'required|numeric|min:0',
            'wallet_list.*.rate_amount' => 'required|numeric|min:1',
            'comment' => 'nullable|string|min:6|max:255',
        ];
    }

    public function messages(): array
    {
        $userName = Auth::user()->name;

        return [
            'wallet_list.*.user_wallet_id.exists' => "Xurmatli $userName foydalanuvchi, o'zingizda mavjud bo'lmagan hisobni tanlayapsiz!"
        ];
    }
}
