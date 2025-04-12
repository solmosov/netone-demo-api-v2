<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartTransProcess extends Model
{
    protected $fillable = [
        'trans_perm_id',
        'sender_depart_id',
        'receiver_depart_id',
        'sender_user_id',
        'receiver_user_id',
        'status_id',
        'comment',
    ];

    public function transferPermission(): BelongsTo
    {
        return $this->belongsTo(TransPerm::class, 'trans_perm_id');
    }

    public function senderDepartment(): BelongsTo
    {
        return $this->belongsTo(Depart::class, 'sender_depart_id');
    }

    public function receiverDepartment(): BelongsTo
    {
        return $this->belongsTo(Depart::class, 'receiver_depart_id');
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function receiverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_user_id');
    }
}
