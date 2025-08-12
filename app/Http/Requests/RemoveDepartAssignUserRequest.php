<?php

namespace App\Http\Requests;

use App\Exceptions\InvalidDataException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class RemoveDepartAssignUserRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'depart_id' => 'required|integer|exists:departs,id',
            'user_id' => 'required|integer|exists:users,id'
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $departId = $this->input('depart_id');
            $userId = $this->input('user_id');

            $exists = DB::table('depart_user')
                ->where('depart_id', $departId)
                ->where('user_id', $userId)
                ->exists();
            if (!$exists) {
                throw new InvalidDataException("Foydalanuvchi bu bo'limga tegishli emas");
            }
        });
    }
}
