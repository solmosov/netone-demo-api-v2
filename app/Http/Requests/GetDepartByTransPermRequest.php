<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GetDepartByTransPermRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'perm_code' => 'required|string|exists:trans_perms,code',
            'depart_id' => [
                'required',
                'integer',
                Rule::exists('depart_user', 'depart_id')
                    ->where('user_id', Auth::id())
            ]
        ];
    }
}
