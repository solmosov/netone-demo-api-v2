<?php

namespace App\Models;

use App\Models\Department\DepartStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Depart extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'comment'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'depart_user', 'depart_id', 'user_id');
    }

    public function departStock(): HasOne
    {
        return $this->hasOne(DepartStock::class, 'depart_id');
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

    public function fromDepartTransfers(): HasMany
    {
        return $this->hasMany(DepartTrans::class, 'from_depart_id');
    }

    public function toDepartTransfers(): HasMany
    {
        return $this->hasMany(DepartTrans::class, 'to_depart_id');
    }

    public function polka(): HasOne
    {
        return $this->hasOne(Polka::class, 'owner_id');
    }
}
