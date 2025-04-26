<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount_type' => $this->amountType,
            'amount' => (float) $this->amount,
            'coefficient' => (float) $this->coefficient,
            'is_change' => (bool) $this->is_change,
            'product' => ProductResource::make($this->product),
            'product_stocks' => ProductStockResource::collection($this->product->stocks),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
