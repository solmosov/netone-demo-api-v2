<?php

namespace App\Services\Production;

use App\Exceptions\InvalidDataException;
use App\Exceptions\ServerErrorException;
use App\Models\Product;
use App\Models\ProductionProcess;
use App\Models\ProductStock;
use App\Models\Status;
use App\Services\Status\StatusService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductionProcessService
{
    public function create(array $data): array
    {
        $reqDepartId = $data['depart_id'];
        $reqProductionRecipeId = $data['production_recipe_id'];
        $reqItemsList = $data['items_list'];

        // Status
        $statusNewProcess = self::getStatusProcess();

        DB::beginTransaction();

        try {
            // New Process
            $newProcess = ProductionProcess::create([
                'depart_id' => $reqDepartId,
                'production_recipe_id' => $reqProductionRecipeId,
                'status_id' => $statusNewProcess->id,
                'out_amount' => 0,
                'cost_price' => 0,
            ]);
            // New Process Items
            $newProcess->processItems()->createMany($reqItemsList);

            // Decrement from stock
            foreach ($reqItemsList as $reqItem) {
                $productId = $reqItem['product_id'];
                $polkaId = $reqItem['polka_id'];
                $amountTypeId = $reqItem['amount_type_id'];
                $amount = $reqItem['amount'];
                $stockItem = ProductStock::where('product_id', $productId)
                    ->where('polka_id', $polkaId)
                    ->firstOrFail();
                if ($stockItem->amount_type_id != $amountTypeId) {
                    throw new InvalidDataException("Zaxira polka o'lchov birligi bilan siz tanlagan o'lchov birligi mos emas!");
                }

                $stockItem->decrement('amount', $amount);
            }

            DB::commit();

            return [
                'message' => "Yangi ishlab chiqarish jarayoni boshlandi"
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function findAll(int|null $departId): Collection
    {
        $query = ProductionProcess::with([
            'depart',
            'productionRecipe',
            'status',
        ]);

        if ($departId) {
            $query->where('depart_id', $departId);
        }

        return $query->latest()->get();
    }

    public function findOne(int $id): ProductionProcess
    {
        return ProductionProcess::with(
            'depart',
            'productionRecipe.recipeItems',
            'processItems',
            'status'
        )->findOrFail($id);
    }

    public function finish(array $data, int $id): array
    {
        // Req Data
        $reqPolkaId = $data['polka_id'];
        $reqTotalAmount = $data['total_amount'];

        // Process
        $productionProcess = ProductionProcess::with([
            'productionRecipe',
            'processItems'
        ])
            ->findOrFail($id);

        // Status completed
        $statusCurrent = StatusService::findById($productionProcess->status_id);

        if ($statusCurrent->code !== 'productionProcess') {
            if ($statusCurrent->code === 'productionCancel') {
                throw new InvalidDataException('Bu ishlab chiqarish jarayoni allaqachon bekor qilingan');
            } else if ($statusCurrent->code === 'productionStopped') {
                throw new InvalidDataException("Bu ishlab chiqarish jarayoni allaqachon to'xtatilgan");
            } else if ($statusCurrent->code === 'productionCompleted') {
                throw new InvalidDataException("Bu ishlab chiqarish jarayoni allaqachon tayyorlangan");
            }

            throw new InvalidDataException("Bu ishlab chiqarish jarayonini tugallab bo'lmaydi");
        }

        // Status productionCompleted
        $statusProductionCompleted = self::getStatusCompleted();

        // Stock
        $stock = ProductStock::where('product_id', $productionProcess->productionRecipe->out_product_id)->firstOrFail();

        // Out Product
        $outProduct = Product::findOrFail($productionProcess->productionRecipe->out_product_id);

        DB::beginTransaction();

        try {
            $productionProcess->status_id = $statusProductionCompleted->id;
            $productionProcess->out_amount = $reqTotalAmount;

            // Change stock
            $stock->increment('amount', $productionProcess->out_amount);

            $productionProcess->save();

            // Cost Price
            $outProductCostPrice = 0;

            foreach ($productionProcess->processItems as $item) {
                // Get Last Receive Product
                // $lastReceive = ReceiveProductDetail::where('product_id', $item->product_id)->latest()->first();
                // if ($lastReceive) {
                //     $costPrice += $lastReceive->price * $item->amount;
                // }

                $product = Product::findOrFail($item->product_id);
                $outProductCostPrice += $product->cost_price * $item->amount;
            }

            $outProductCostPrice = $outProductCostPrice / $reqTotalAmount;

            // Set Cost Price
            $productionProcess->cost_price = $outProductCostPrice;
            $productionProcess->save();

            // Set Cost Price
            $outProduct->cost_price = $outProductCostPrice;
            $outProduct->save();

            DB::commit();

            return [
                'message' => "$id. Ishlab chiqarish jarayoni yakunlandi"
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public static function getStatusProcess(): Status
    {
        return StatusService::findByCode('productionProcess');
    }

    private static function getStatusCancel(): Status
    {
        return StatusService::findByCode('productionCancel');
    }

    private static function getStatusStopped(): Status
    {
        return StatusService::findByCode('productionStopped');
    }

    private static function getStatusCompleted(): Status
    {
        return StatusService::findByCode('productionCompleted');
    }
}
