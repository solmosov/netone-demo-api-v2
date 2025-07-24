<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'depart' => DepartResource::make($this->whenLoaded('depart')),
            'polka' => PolkaResource::make($this->whenLoaded('polka')),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'amount' => (float)$this->amount,
            'amount_type' => $this->whenLoaded('amountType'),
            'min_amount' => (float) $this->min_amount,
            'max_amount' => (float) $this->max_amount,
            'percentage' => $this->percentage != null ? (float) $this->percentage : null,
            'total_price' => (float) $this->total_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
