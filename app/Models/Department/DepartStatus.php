<?php

namespace App\Models\Department;

use App\Models\Depart;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartStatus extends Model
{
    protected $fillable = [
        'depart_id',
        'name',
    ];

    public function depart():BelongsTo
    {
        return $this->belongsTo(Depart::class, 'depart_id');
    }

}
