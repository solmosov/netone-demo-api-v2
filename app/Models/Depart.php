<?php

namespace App\Models;

use App\Models\Department\DepartStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Depart extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'comment'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transPerms(): BelongsToMany
    {
        return $this->belongsToMany(
            TransPerm::class,
            'depart_trans_perm_property',
            'depart_id',
            'trans_perm_id'
        );
    }

    public function departStatuses(): HasMany
    {
        return $this->hasMany(
            DepartStatus::class,
            'depart_id',
        );
    }
}
