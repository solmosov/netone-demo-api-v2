<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionPlanItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'depart' => DepartResource::make($this->depart),
            'recipe_item' => RecipeItemResource::make($this->recipeItem),
            'depart_status' => DepartStatusResource::make($this->departStatus),
            'status'=> $this->status,
            'product' => ProductResource::make($this->product),
            'amount_type' => $this->amountType,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
