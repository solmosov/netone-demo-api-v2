<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartTrans extends Model
{

    protected $fillable = [
        'from_depart_id',
        'to_depart_id',
        'comment',
    ];

    public function fromDepart(): BelongsTo
    {
        return $this->belongsTo(Depart::class, 'from_depart_id');
    }

    public function toDepart(): BelongsTo
    {
        return $this->belongsTo(Depart::class, 'to_depart_id');
    }
}
