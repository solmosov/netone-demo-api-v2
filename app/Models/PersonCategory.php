<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonCategory extends Model
{
    /** @use HasFactory<\Database\Factories\PersonCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(PersonCategory::class, 'person_category_id');
    }
}
