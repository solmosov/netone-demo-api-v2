<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProducerProductTransShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'producer' => UserResource::make($this->whenLoaded('producer')),
            'depart' => DepartResource::make($this->whenLoaded('depart')),
            'depart_user' => UserResource::make($this->whenLoaded('departUser')),
            'status' => $this->whenLoaded('status'),
            'details' => ProducerProductTransDetailResource::collection($this->details),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
