<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProducerProductTransDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_stock' => ProductStockResource::make($this->whenLoaded('productStock')),
            'depart_stock' => DepartStockResource::make($this->whenLoaded('departStock')),
            'amount' => (float) $this->amount,
            'amount_type' => $this->amountType,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
