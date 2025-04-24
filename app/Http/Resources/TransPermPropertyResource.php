<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransPermPropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trans_perm' => TransPermResource::make($this->transPerm),
            'details' => TransPermPropertyDetailsResource::collection($this->details),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
