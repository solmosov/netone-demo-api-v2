<?php

namespace App\Services\Stock;

use App\Exceptions\ServerErrorException;
use App\Models\DepartStock;
use App\Models\Polka;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DepartStockService
{
    public function create(array $data)
    {
        // Req Data
        $departId = $data['depart_id'];
        $polkaId = $data['polka_id'];
        $productId = $data['product_id'];
        $minAmount = $data['min_amount'] ?? 0;
        $maxAmount = $data['max_amount'] ?? 0;

        // Product
        $product = Product::findOrFail($productId);
        $amountTypeId = $product->price_amount_type_id;

        // Polka
        $polka = Polka::where('id', $polkaId)
            ->firstOrFail();

        DB::beginTransaction();

        try {
            // New Depart Stock
            $newDepartStock = DepartStock::create([
                'depart_id' => $departId,
                'polka_id' => $polkaId,
                'product_id' => $productId,
                'amount_type_id' => $amountTypeId,
                'min_amount' => $minAmount,
                'max_amount' => $maxAmount
            ]);

            // Change of Polka Owner
            $polka->owner_id = $departId;
            $polka->save();

            DB::commit();

            return $newDepartStock;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function findAll(array $queryParams): Collection
    {
        // Query Params
        $departId = $queryParams['depart_id'] ?? null;
        $isMin = $queryParams['is_min'] ?? null;
        $isMax = $queryParams['is_max'] ?? null;

        $query = DepartStock::with(['depart', 'polka', 'amountType', 'product'])
            ->selectRaw('*,
                SUM(amount * products.sale_price) as total_price,
                CASE
                    WHEN max_amount=0 AND min_amount=0 THEN NULL
                    WHEN max_amount = min_amount THEN 0
                    ELSE ((amount - min_amount) / (max_amount - min_amount) * 100)
                END as percentage
            ')
            ->leftJoin('products', 'depart_stocks.product_id', '=', 'products.id')
            ->groupBy('depart_stocks.id');

        if ($departId) {
            $query->where('depart_id', $departId);
        }

        if ($isMin) {
            $query->where('amount', '<=', 'min_amount');
        }

        if ($isMax) {
            $query->where('amount', '>=', 'max_amount');
        }

        return $query->get();
    }

    public function findOne(int $id): DepartStock
    {
        return DepartStock::with(['depart', 'polka', 'amountType', 'product'])
            ->selectRaw('*,
                SUM(amount * products.sale_price) as total_price,
                CASE
                    WHEN max_amount=0 AND min_amount=0 THEN NULL
                    WHEN max_amount = min_amount THEN 0
                    ELSE ((amount - min_amount) / (max_amount - min_amount) * 100)
                END as percentage
            ')
            ->leftJoin('products', 'depart_stocks.product_id', "=", 'products.id')
            ->groupBy('depart_stocks.id')
            ->findOrFail($id);
    }

    public function delete(int $id): DepartStock
    {
        $departStock  = $this->findOne($id);

        try {
            $departStock->delete();

            return $departStock;
        } catch (\Exception $e) {
            throw new ServerErrorException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
