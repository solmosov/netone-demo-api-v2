<?php

namespace App\Services\Production;

use App\Exceptions\ServerErrorException;
use App\Models\Depart;
use App\Models\Department\DepartStatus;
use App\Models\ProductionPlan;
use App\Models\ProductionPlanItem;
use App\Models\ProductionRecipe;
use App\Models\RecipeItems;
use App\Services\Status\StatusService;
use Illuminate\Support\Facades\DB;

class ProductionPlanService
{

    public function findAll()
    {
        $data = ProductionPlan::with([
            'recipe.outAmountType',
            'amountType',
            'status'
        ])
            ->get();

        return [
            'data' => $data
        ];
    }

    public function create(array $data)
    {
        // Req Data
        $recipeId = $data['recipe_id'];
        $name = $data['name'];
        $amount = $data['amount'];
        $amountTypeId = $data['amount_type_id'];
        $comment = $data['comment'] ?? null;
        $planItemList = $data['plan_item_list'];

        // Recipe
        $recipe = ProductionRecipe::with('recipeItems')->findOrFail($recipeId);
        $pluckRecipeItemCoefficient = $recipe->recipeItems->pluck('coefficient');
        $pluckRecipeItemProduct = $recipe->recipeItems->pluck('product', 'id');

        // Plan item status
        $planItemStatus = self::getPlanItemNewStatus();

        DB::beginTransaction();
        try {
            // New Production Plan
            $newProductionPlan = ProductionPlan::create([
                'recipe_id' => $recipeId,
                'amount_type_id' => $amountTypeId,
                'status_id' => self::getNewStatus()->id,
                'name' => $name,
                'amount' => $amount,
                'comment' => $comment
            ]);


            // Plan Items
            $itemsList = [];

            foreach ($planItemList as $item) {
                $departId = $item['depart_id'];
                $recipeItemId = $item['recipe_item_id'];
                $productId = $pluckRecipeItemProduct[$recipeItemId]->id;
                $itemAmountTypeId = $pluckRecipeItemProduct[$recipeItemId]->price_amount_type_id;

                // Recipe
                $coefficient = $pluckRecipeItemCoefficient[$recipeItemId];
                $calcAmount = $amount * $coefficient;

                // Depart
                $departStatus = DepartStatus::where('depart_id', $departId)->firstOrFail();

                $itemsList[] = [
                    'plan_id' => $newProductionPlan->id,
                    'depart_id' => $departId,
                    'recipe_item_id' => $recipeItemId,
                    'depart_status_id' => $departStatus->id,
                    'status_id' => $planItemStatus->id,
                    'product_id' => $productId,
                    'amount' => $calcAmount,
                    'amount_type_id' => $itemAmountTypeId,
                ];
            }

            $newProductionPlan->planItems()->createMany($itemsList);

            // Finish
            DB::commit();

            return [
                'message' => "Yangi `$newProductionPlan->name` qo'shildi",
                'code' => 201,
            ];
        } catch (\Exception $e) {

            DB::rollBack();
            return [
                'message' => $e->getMessage(),
                'code' => 422
            ];
        }
    }

    public function findOne(int $id): array
    {
        $data = ProductionPlan::with([
            'planItems',
            'recipe',
            'amountType',
            'status'
        ])->findOrFail($id);

        return [
            'data' => $data
        ];
    }

    public static function getNewStatus()
    {
        return StatusService::findByCode('productionPlanNew');
    }

    public static function getPlanItemNewStatus()
    {
        return StatusService::findByCode('productionPlanDepartNew');
    }
    public static function getPlanItemReceivedStatus()
    {
        return StatusService::findByCode('productionPlanDepartReceived');
    }

    public static function getPlanItemCompletedStatus()
    {
        return StatusService::findByCode('productionPlanDepartCompleted');
    }
}
