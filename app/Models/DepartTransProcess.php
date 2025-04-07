<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartTransProcess extends Model
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
}
