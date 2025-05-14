<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProducerProductTransDetails extends Model
{
    protected $fillable = [
        'trans_id',
        'product_stock_id', // not required
        'depart_stock_id', // not required
        'amount_type_id',
        'amount'
    ];

    public function trans(): BelongsTo
    {
        return $this->belongsTo(ProducerProductTrans::class, 'trans_id');
    }

    public function productStock(): BelongsTo
    {
        return $this->belongsTo(ProductStock::class, 'product_stock_id')
            ->with(['product', 'polka', 'amountType']);
    }

    public function departStock(): BelongsTo
    {
        return $this->belongsTo(DepartStock::class, 'depart_stock_id')
            ->with(['polka', 'amountType', 'product']);
    }

    public function amountType(): BelongsTo
    {
        return $this->belongsTo(AmountType::class, 'amount_type_id');
    }
}
