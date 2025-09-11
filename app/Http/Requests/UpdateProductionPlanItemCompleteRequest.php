<?php

namespace App\Http\Requests;

use App\Models\ProductionPlan;
use App\Models\ProductionPlanItem;
use App\Services\Production\ProductionPlanService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class UpdateProductionPlanItemCompleteRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::user()->isDepartUser();
    }


    public function rules(): array
    {
        return [
            'depart_id' => 'required|exists:departs,id',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $departId = $this->input('depart_id');
            $planItemId = $this->route('plan_item_id');

            $exists = DB::table('depart_user')->where('depart_id', $departId)->where('user_id', Auth::id())->exists();
            if (!$exists) {
                $validator->errors()->add('depart_id', "O'zingizga tegishli bo'lmagan bo'limni tanlamoqdasiz");
            }

            $planItem = ProductionPlanItem::where('id', $planItemId)
                ->where('depart_id', $departId)
                ->first();
            if (!$planItem) {
                $validator->errors()->add('depart_id', "Bo'lim noto'g'ri tanlandi");
            } else {
                $exists = ProductionPlan::where('id', $planItem->plan_id)
                    ->whereIn('status_id', [
                        ProductionPlanService::getNewStatus()->id,
                        ProductionPlanService::getProcessStatus()->id,
                    ])
                    ->exists();
                if (!$exists) {
                    $validator->errors()->add('depart_id', "Bu topshiriq allaqachon yakunlangan");
                }
            }
        });
    }
}
