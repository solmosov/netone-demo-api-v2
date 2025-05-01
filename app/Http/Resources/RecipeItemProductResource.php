<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeItemProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cost_price' => $this->cost_price,
            'sale_price' => $this->sale_price,
            'receive_price' => $this->receive_price,
            'amount_type' => $this->priceAmountType,
            'stocks' => ProductStockResource::collection($this->stocks),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
