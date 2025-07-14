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

        $query = DepartStock::with(['depart', 'polka', 'amountType', 'product'])
            ->selectRaw('depart_stocks.*, SUM(amount * products.sale_price) as total_price')
            ->leftJoin('products', 'depart_stocks.product_id', '=', 'products.id')
            ->groupBy('depart_stocks.id');;

        if ($departId) {
            $query->where('depart_id', $departId);
        }

        return $query->get();
    }

    public function findOne(int $id): DepartStock
    {
        return DepartStock::with(['depart', 'polka', 'amountType', 'product'])->findOrFail($id);
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
