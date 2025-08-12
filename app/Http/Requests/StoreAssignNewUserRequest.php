<?php

namespace App\Http\Requests;

use App\Exceptions\InvalidDataException;
use App\Services\Department\DepartService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class StoreAssignNewUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'depart_id' => 'required|integer|exists:departs,id',
            'user_id' => 'required|integer|exists:users,id',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $exists = DB::table('depart_user')
                ->where('depart_id', $this->input('depart_id'))
                ->where('user_id', $this->input('user_id'))
                ->exists();
            if ($exists) {
                throw new InvalidDataException("Bu foydalanuvchi bo'limda allaqachon mavjud");
            }
        });
    }
}
