<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartTransProcessShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transfer_permission' => TransPermResource::make($this->transferPermission),
            'sender_department' => DepartResource::make($this->senderDepartment),
            'receiver_department' => DepartResource::make($this->receiverDepartment),
            'sender_user' => UserResource::make($this->senderUser),
            'receiver_user' => UserResource::make($this->receiverUser),
            'details' => DepartTransProcessDetailsResource::collection($this->details),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
