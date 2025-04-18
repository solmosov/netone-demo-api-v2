<?php

namespace App\Models;

use App\Models\Department\DepartStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionPlanItem extends Model
{
    protected $fillable = [
        'plan_id',
        'depart_id',
        'recipe_item_id',
        'depart_status_id',
        'status_id',
        'product_id',
        'amount_type_id',
        'amount',
        'comment',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class, 'plan_id');
    }

    public function depart(): BelongsTo
    {
        return $this->belongsTo(Depart::class, 'depart_id');
    }

    public function recipeItem(): BelongsTo
    {
        return $this->belongsTo(RecipeItems::class, 'recipe_item_id');
    }

    public function departStatus(): BelongsTo
    {
        return $this->belongsTo(DepartStatus::class, 'depart_status_id');
    }

    public function status():BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


    public function amountType(): BelongsTo
    {
        return $this->belongsTo(AmountType::class, 'amount_type_id');
    }
}
