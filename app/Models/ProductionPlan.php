<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionPlan extends Model
{
    protected $fillable = [
        'recipe_id',
        'amount_type_id',
        'status_id',
        'name',
        'amount',
        'comment',
        'finish_date',
    ];
}
