<?php

namespace App\Http\Requests;

use App\Rules\WalletBelongsToUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
            'wallet_id' => [
                'required',
                'integer',
                'exists:user_wallet,wallet_id,user_id,' . Auth::id(),
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
}
