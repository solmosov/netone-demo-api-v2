<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GetPlanItemsByDepartRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'depart_id' => [
                'nullable',
                'integer',
                Rule::exists('depart_user', 'depart_id')->where('user_id', Auth::id())
            ],
            'plan_id' => 'nullable|integer|exists:production_plans,id',
            'status_code' => 'nullable|string|exists:statuses,code', /* productionPlanDepartNew, productionPlanDepartReceived, productionPlanDepartCompleted */
        ];
    }
}
