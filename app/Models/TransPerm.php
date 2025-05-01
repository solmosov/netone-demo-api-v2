<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransPerm extends Model
{

    protected $fillable = [
        'name',
        'code'
    ];

    public function transPermProperty(): HasMany
    {
        return $this->hasMany(DepartTransPermProperty::class,'trans_perm_id');
    }

    public function departs(): BelongsToMany
    {
        return $this->belongsToMany(
            Depart::class,
            'depart_trans_perm_property',
            'trans_perm_id',
            'depart_id'
        );
    }

    public function departTransProcesses(): HasMany
    {
        return $this->hasMany(DepartTransProcess::class, 'trans_perm_id');
    }
}
