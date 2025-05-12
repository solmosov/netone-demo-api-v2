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
            'sender_depart_stock' => DepartStockResource::make($this->senderDepartStock),
            'receiver_depart_stock' => DepartStockResource::make($this->receiverDepartStock),
            'amount' => (float) $this->amount,
            'amount_type' => $this->amountType,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
