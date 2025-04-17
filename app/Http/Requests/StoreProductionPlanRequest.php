<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductionPlanRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'recipe_id' => 'required|integer|exists:production_recipes,id',
            'name'  => 'required|string',
            'amount'  => 'required|min:0',
            'amount_type_id'  => 'required|integer|exists:amount_types,id',
            'comment'  => 'nullable|min:6|max:255',
            'plan_item_list' => 'required|array',
            'plan_item_list.*.recipe_item_id' => 'required|integer|exists:recipe_items,id',
            'plan_item_list.*.depart_id' => 'required|integer|exists:departs,id',
        ];
    }
}
