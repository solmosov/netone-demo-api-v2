<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionPlanShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => (float) $this->amount,
            'amount_type' => $this->amountType,
            'finish_date' => $this->finish_date,
            'comment' => $this->comment,
            'recipe' => RecipeResource::make($this->recipe),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'plan_items' => ProductionPlanItemResource::collection($this->planItems),
            'plan_items_by_status' => $this->transformPlanItemsByStatuses($this->plan_items_by_statuses)
        ];
    }

    private function transformPlanItemsByStatuses($statuses)
    {
        return $statuses->map(function ($status) {
            return [
                "id" => $status['id'],
                "name" => $status['name'],
                "code" => $status['code'],
                "items" => ProductionPlanItemResource::collection($status['items']),
            ];
        });
    }
}
