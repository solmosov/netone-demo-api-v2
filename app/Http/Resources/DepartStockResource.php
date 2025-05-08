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
            'depart' => DepartResource::make($this->depart),
            'polka' => PolkaResource::make($this->polka),
            'product' => ProductResource::make($this->product),
            'amount' => (float)$this->amount,
            'amount_type' => $this->amountType,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
