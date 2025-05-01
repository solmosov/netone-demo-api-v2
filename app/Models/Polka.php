<?php

namespace App\Models;

use App\Models\Department\DepartTransProcessDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Polka extends Model
{
    /** @use HasFactory<\Database\Factories\PolkaFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'owner_id'
    ];

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->with('children');
    }

    public function ownerDepart(): BelongsTo
    {
        return $this->belongsTo(Depart::class, 'owner_id');
    }

    public function departTransProcessDetail(): HasMany
    {
        return $this->hasMany(DepartTransProcessDetail::class, 'polka_id');
    }
}
