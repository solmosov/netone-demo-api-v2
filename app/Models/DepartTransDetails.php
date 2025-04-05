<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartTransDetails extends Model
{
    protected $fillable = [
        'depart_trans_id',
        'product_id',
        'amount_type_id',
        'amount',
    ];

    public function departTrans()
    {
        return $this->belongsTo(DepartTrans::class, 'depart_trans_id');
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
