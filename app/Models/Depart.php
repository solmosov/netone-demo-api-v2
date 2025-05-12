<?php

namespace App\Models;

use App\Models\Department\DepartStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Depart extends Model
{
    protected $fillable = [
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

    public function departStatus(): HasOne
    {
        return $this->hasOne(
            DepartStatus::class,
            'depart_id',
        );
    }

    public function polka(): HasOne
    {
        return $this->hasOne(Polka::class, 'owner_id');
    }

    public function fromDepartProcess(): HasMany
    {
        return $this->hasMany(DepartTransProcess::class, 'from_depart_id');
    }

    public function toDepartProcess(): HasMany
    {
        return $this->hasMany(DepartTransProcess::class, 'to_depart_id');
    }

    public function productionPlanItems(): HasMany
    {
        return $this->hasMany(ProductionPlanItem::class, 'depart_id');
    }

    public function transPermProperty(): HasMany
    {
        return $this->hasMany(DepartTransPermProperty::class, 'depart_id')
            ->with(['transPerm', 'details']);
    }

    public function departTransPropertyDetails(): HasMany
    {
        return $this->hasMany(DepartTransPermPropertyDetails::class, 'depart_id');
    }
}
