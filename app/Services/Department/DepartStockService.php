<?php

namespace App\Services\Department;

use App\Exceptions\ServerErrorException;
use App\Models\DepartStock;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class DepartStockService
{
    public function create(array $data): array
    {
        // Req Data
        $departId = $data['depart_id'];
        $polkaId = $data['polka_id'];
        $productId = $data['product_id'];

        // Product
        $product = Product::findOrFail($productId);
        $amountTypeId = $product->price_amount_type_id;

        // New Depart Stock
        $newDepartStock = DepartStock::create([
            'depart_id' => $departId,
            'polka_id' => $polkaId,
            'product_id' => $productId,
            'amount_type_id' => $amountTypeId,
        ]);

        return [
            'message' => "Yangi bo'lim zaxira qo'shildi",
            'data' => $newDepartStock
        ];
    }

    public function findAll(): Collection
    {

        return DepartStock::with(['depart','polka','amountType','product'])->get();
    }

    public function findOne(int $id): DepartStock
    {
        return DepartStock::with(['depart','polka','amountType','product'])->findOrfail($id);
    }

    public function delete(int $id): DepartStock
    {
        $departStock  = $this->findOne($id);

        try{
            $departStock->delete();

            return $departStock;
        }catch (\Exception $e){
            throw new ServerErrorException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
