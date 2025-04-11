<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_list' => UserResource::collection($this->whenLoaded('users')),
            'depart_stock' => ProductStockResource::make($this->departStock),
            'polka' => $this->polka,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
