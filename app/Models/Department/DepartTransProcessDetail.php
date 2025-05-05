<?php

namespace App\Models\Department;

use App\Models\AmountType;
use App\Models\DepartStock;
use App\Models\DepartTransProcess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartTransProcessDetail extends Model
{
    protected $fillable = [
        'depart_trans_process_id',
        'sender_depart_stock_id',
        'receive_depart_stock_id',
        'amount_type_id',
        'amount',
    ];

    public function departTransProcess(): BelongsTo
    {
        return $this->belongsTo(DepartTransProcess::class, 'depart_trans_process_id');
    }

    public function amountType(): BelongsTo
    {
        return $this->belongsTo(AmountType::class, 'amount_type_id');
    }

    public function senderDepartStock(): BelongsTo
    {
        return $this->belongsTo(DepartStock::class, 'sender_depart_stock_id');
    }
    public function receiverDepartStock(): BelongsTo
    {
        return $this->belongsTo(DepartStock::class, 'receiver_depart_stock_id');
    }
}
