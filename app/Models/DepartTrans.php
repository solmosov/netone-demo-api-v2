<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartTrans extends Model
{

    protected $fillable = [
        'user_id',
        'trans_perm_id',
        'from_depart_id',
        'to_depart_id',
        'comment',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transPerm(): BelongsTo
    {
        return $this->belongsTo(TransPerm::class, 'trans_perm_id');
    }

    public function fromDepart(): BelongsTo
    {
        return $this->belongsTo(Depart::class, 'from_depart_id');
    }

    public function toDepart(): BelongsTo
    {
        return $this->belongsTo(Depart::class, 'to_depart_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DepartTransDetails::class, 'depart_trans_id');
    }
}
