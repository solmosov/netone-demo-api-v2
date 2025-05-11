<?php

namespace App\Services\Production;

use App\Exceptions\InvalidDataException;
use App\Exceptions\ServerErrorException;
use App\Models\DepartStock;
use App\Models\Product;
use App\Models\ProductionProcess;
use App\Models\ProductStock;
use App\Models\RecipeItems;
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

            $attachItems = [];

            // Decrement from stock
            foreach ($reqItemsList as $reqItem) {
                $recipeItemId = $reqItem['recipe_item_id'];
                $departStockId = $reqItem['depart_stock_id'];
                $amount = $reqItem['amount'];

                // Recipe Item
                $recipeItem = RecipeItems::findOrFail($recipeItemId);

                // Department Stock
                $departStockItem = DepartStock::where('product_id', $recipeItem->product_id)
                    ->where('amount_type_id', $recipeItem->amount_type_id)
                    ->findOrFail($departStockId);


                $attachItems[] = [
                    'production_process_id' => $reqProductionRecipeId,
                    'product_id' => $recipeItem->product_id,
                    'amount_type_id' => $recipeItem->amount_type_id,
                    'amount' => $amount,
                ];

                // Department Stock
                $departStockItem->decrement('amount', $amount);
            }


            // New Process Items
            $newProcess->processItems()->createMany($attachItems);

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
        $reqDepartStockId = $data['depart_stock_id'];
        $reqOutAmount = $data['out_amount'];

        // Process
        $productionProcess = ProductionProcess::with([
            'productionRecipe',
            'processItems'
        ])
            ->findOrFail($id);
        $outProductId = $productionProcess->productionRecipe->out_product_id;


        // Status Completed
        $statusCompleted = self::getStatusCompleted();

        // Depart Stock
        $departStock = ProductStock::where('product_id', $outProductId)
            ->findOrFail($reqDepartStockId);

        // Out Product
        $outProduct = Product::findOrFail($outProductId);

        DB::beginTransaction();

        try {
            // Change Process Status And Out Amount
            $productionProcess->status_id = $statusCompleted->id;
            $productionProcess->out_amount = $reqOutAmount;

            // Change Depart Stock Amount
            $departStock->increment('amount', $productionProcess->out_amount);

            $productionProcess->save();

            // Cost Price
            $totalOutPrice = 0;

            foreach ($productionProcess->processItems as $item) {
                $product = Product::findOrFail($item->product_id);

                $totalOutPrice += $product->cost_price * $item->amount;
            }

            // Calc Cost Price
            $outProductCostPrice = $totalOutPrice / $reqOutAmount;

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
