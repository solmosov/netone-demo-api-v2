<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class GetProductionPlanRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::user()->isProducer();
    }


    public function rules(): array
    {
        return [
            'recipe_id' => 'nullable|integer|exists:production_recipes,id',
            'status_code' => 'nullable|string|exists:statuses,code',
            /* productionPlanNew, productionPlanProcess, productionPlanCancel, productionPlanCompleted */
        ];
    }
}
