<?php

namespace App\Services\Stock;

use App\Models\ProductStock;
use Illuminate\Database\Eloquent\Collection;

class ProductStockService
{
    public function findAll(): Collection
    {
        return ProductStock::with('polka', 'product', 'amountType')
            ->selectRaw('*,
                CASE
                    WHEN max_amount=0 AND min_amount=0 THEN NULL
                    WHEN max_amount = min_amount THEN 0
                    ELSE ((amount - min_amount) / (max_amount - min_amount) * 100)
                END as percentage')
            ->get();
    }

    public function findOne(int $id)
    {
        return ProductStock::with('polka', 'product', 'amountType')
            ->selectRaw('*,
                CASE
                    WHEN max_amount=0 AND min_amount=0 THEN NULL
                    WHEN max_amount = min_amount THEN 0
                    ELSE ((amount - min_amount) / (max_amount - min_amount) * 100)
                END as percentage')
            ->findOrFail($id);
    }

    public function update(array $data, int $id)
    {

    }

    public function delete(int $id)
    {

    }

}
