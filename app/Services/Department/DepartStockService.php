<?php

namespace App\Services\Department;

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
            Polka::where('id', $polkaId)
                ->where('polka_id', $polkaId)
                ->firstOrFail()
                ->update('owner_id', $newDepartStock->id);

            DB::commit();

            return $newDepartStock;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function findAll(): Collection
    {

        return DepartStock::with(['depart', 'polka', 'amountType', 'product'])->get();
    }

    public function findOne(int $id): DepartStock
    {
        return DepartStock::with(['depart', 'polka', 'amountType', 'product'])->findOrfail($id);
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
