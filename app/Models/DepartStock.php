<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartStock extends Model
{
    //

    protected $fillable = [
        'depart_id',
        'polka_id',
        'amount_type_id',
        'product_id',
        'amount'
    ];

    public function depart(): BelongsTo
    {
        return $this->belongsTo(Depart::class, 'depart_id');
    }

    public function polka(): BelongsTo
    {
        return $this->belongsTo(Polka::class, 'polka_id');
    }

    public function amountType(): BelongsTo
    {
        return $this->belongsTo(AmountType::class, 'amount_type_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
