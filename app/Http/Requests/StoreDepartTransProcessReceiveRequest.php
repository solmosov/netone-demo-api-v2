<?php

namespace App\Http\Requests;

use App\Models\Department\DepartTransProcessDetail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDepartTransProcessReceiveRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'details_list' => 'required|array',
            'details_list.*.item_id' => 'required|integer|exists:depart_trans_process_details,id',
            'details_list.*.polka_id' => 'required|integer|exists:polkas,id'
        ];
    }
}
