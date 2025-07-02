<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ReconciliationCustomerDetailsDebitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authUser =Auth::user();
        return [
            'id' => $this->id,
            'user' => UserResource::make($this->user),
            'total_cost_price' => $authUser->isAdmin()?(float)$this->total_cost_price:0,
            'total_sale_price' => (float) $this->total_sale_price,
            'order' => OrderResource::make($this->order),
            'order_details' => OrderDetailResource::collection($this->order->orderDetails),
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
