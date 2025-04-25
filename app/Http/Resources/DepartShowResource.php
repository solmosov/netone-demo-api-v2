<?php

namespace App\Http\Resources;

use App\Models\DepartStock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_list' => UserResource::collection($this->whenLoaded('users')),
            'polka' => $this->polka,
            'trans_perm_properties' => TransPermPropertyResource::collection($this->transPermProperty),
            'depart_status' => DepartStatusResource::make($this->departStatus),
            'depart_stock' => ProductStockResource::make($this->departStock),
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
