<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartTransPermProperty extends Model
{
    protected $table = "depart_trans_perm_property";

    protected $fillable = [
        'trans_perm_id',
        'depart_id',
    ];


    public function transPerm(): BelongsTo
    {
        return $this->belongsTo(TransPerm::class, 'trans_perm_id');
    }

    public function depart(): BelongsTo
    {
        return $this->belongsTo(Depart::class, 'depart_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DepartTransPermPropertyDetails::class, 'property_id')
            ->with(['depart']);
    }
}
