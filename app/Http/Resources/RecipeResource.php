<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'out_product' => ProductResource::make($this->whenLoaded('outProduct')),
            'out_amount_type' => $this->outAmountType,
            'out_amount' => (float) $this->out_amount,
            'recipe_items' => RecipeItemResource::collection($this->whenLoaded('recipeItems')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
