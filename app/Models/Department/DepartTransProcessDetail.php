<?php

namespace App\Models\Department;

use App\Models\AmountType;
use App\Models\DepartTransProcess;
use App\Models\Polka;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartTransProcessDetail extends Model
{
    protected $fillable = [
        'depart_trans_process_id',
        'product_id',
        'polka_id',
        'received_polka_id',
        'amount_type_id',
        'amount',
    ];

    public function departTransProcess(): BelongsTo
    {
        return $this->belongsTo(DepartTransProcess::class, 'depart_trans_process_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function polka(): BelongsTo
    {
        return $this->belongsTo(Polka::class, 'polka_id');
    }

    public function receivedPolka(): BelongsTo
    {
        return $this->belongsTo(Polka::class, 'received_polka_id');
    }

    public function amountType(): BelongsTo
    {
        return $this->belongsTo(AmountType::class, 'amount_type_id');
    }
}
