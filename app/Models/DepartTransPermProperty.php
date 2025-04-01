<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DepartTransPermProperty extends Model
{
    protected $table = "depart_trans_perm_property";

    protected $fillable = [
        'trans_perm_id',
        'depart_id',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(DepartTransPermPropertyDetails::class, 'property_id');
    }
}
