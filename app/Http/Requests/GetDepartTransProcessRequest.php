<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetDepartTransProcessRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'trans_perm_id' => 'nullable|integer|exists:trans_perms,id',
            'trans_perm_code' => 'nullable|string|exists:trans_perms,code',
            'sender_depart_id' => 'nullable|integer|exists:departments,id',
            'receiver_depart_id' => 'nullable|integer|exists:departments,id',
            'sender_user_id' => 'nullable|integer|exists:users,id',
            'receiver_user_id' => 'nullable|integer|exists:users,id',
            'status_id' => 'nullable|integer|exists:statuses,id',
            'status_code' => 'nullable|string|exists:statuses,code',
        ];
    }
}
