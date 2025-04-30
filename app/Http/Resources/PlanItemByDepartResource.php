<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanItemByDepartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plan' =>ProductionPlanResource::make($this->plan),
            'depart' => DepartResource::make($this->depart),
            'depart_status' => DepartStatusResource::make($this->departStatus),
            'status' => $this->status,
            'recipe_item' => RecipeItemResource::make($this->recipeItem),
            'product' => ProductResource::make($this->product),
            'amount' => (float)$this->amount,
            'amount_type' => $this->amountType,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
