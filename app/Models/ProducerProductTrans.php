<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProducerProductTrans extends Model
{
    protected $fillable = [
        'producer_id',
        'depart_id',
        'depart_user_id', // nullable
        'status_id',
        'is_sending_depart',
        'comment',
    ];

    public function producer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'producer_id');
    }

    public function depart(): BelongsTo
    {
        return $this->belongsTo(Depart::class, 'depart_id');
    }

    public function departUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'depart_user_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ProducerProductTransDetails::class, 'trans_id');
    }
}
