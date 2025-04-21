<?php

namespace App\Services\Production;

use App\Exceptions\InvalidDataException;
use App\Exceptions\ServerErrorException;
use App\Models\ProductionProcess;
use App\Models\ProductStock;
use App\Models\Status;
use App\Services\Status\StatusService;
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
