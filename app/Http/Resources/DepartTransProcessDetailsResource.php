<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartTransProcessDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => ProductResource::make($this->product),
            'polka' => PolkaResource::make($this->polka),
            'receive_polka' => PolkaResource::make($this->receivedPolka),
            'amount' => (float) $this->amount,
            'amount_type' => $this->amountType,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
