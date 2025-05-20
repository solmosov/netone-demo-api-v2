<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransPermPropertyDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'depart' => DepartResource::make($this->depart),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
