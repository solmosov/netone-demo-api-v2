<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonCategory extends Model
{
    /** @use HasFactory<\Database\Factories\PersonCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name'
    ];
}
