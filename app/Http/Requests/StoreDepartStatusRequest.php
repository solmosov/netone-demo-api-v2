<?php

namespace App\Http\Requests;

use App\Models\Department\DepartStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDepartStatusRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'depart_id' => 'required|numeric|exists:departs,id',
            'name' => 'required|string|unique:depart_statuses',
        ];
    }

    public function messages():array
    {
        return [
            'name.required' => "Bu bo'lim status allaqachon mavjud",
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function($validator){
            $departId = $this->input('depart_id');
            
            if(DepartStatus::where('depart_id', $departId)->first()){
                $validator->errors()->add('depart_id', "Bu bo'lim uchun allaqachon status mavjud");
            }
        });
    }
}
