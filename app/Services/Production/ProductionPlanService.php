<?php

namespace App\Services\Production;

use App\Events\ProductionPlanCreated;
use App\Exceptions\ServerErrorException;
use App\Models\Department\DepartStatus;
use App\Models\ProductionPlan;
use App\Models\ProductionPlanItem;
use App\Models\ProductionRecipe;
use App\Models\Status;
use App\Models\User;
use App\Services\Status\StatusService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class ProductionPlanService
{

    public function findAll(array $queryParams): array
    {
        $query = ProductionPlan::with([
            'recipe.outAmountType',
            'amountType',
            'status'
        ]);

        foreach ($queryParams as $key => $item) {
            if ($key === 'status_code') {
                $status = StatusService::findByCode($item);
                $query->where('status_id', $status->id);
            } else {
                $query->where($key, $item);
            }
        }

        $data = $query
            ->latest()
            ->limit(200)
            ->get();

        return [
            'data' => $data
        ];
    }

    public function create(array $data): array
    {
        // Req Data
        // $recipeId = $data['recipe_id'];
        // $name = $data['name'];
        // $amount = $data['amount'];
        // $amountTypeId = $data['amount_type_id'];
        // $comment = $data['comment'] ?? null;
        $planItemList = $data['plan_item_list'];

        DB::beginTransaction();
        try {
            // New Production Plan
            $newProductionPlan = $this->createWithoutTransaction($data);

            // Finish
            DB::commit();


            $departsIds = array_column($planItemList, 'depart_id');

            // Send Notifications
            $this->sendNotificationsToDepartments($newProductionPlan, $departsIds);

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

    public function createWithoutTransaction(array $data)
    {
        // Data
        $recipeId = $data['recipe_id'];
        $name = $data['name'];
        $amount = $data['amount'];
        $amountTypeId = $data['amount_type_id'];
        $comment = $data['comment'] ?? null;
        $planItemList = $data['plan_item_list']; // [recipe_item_id, depart_id]

        // Recipe
        $recipe = ProductionRecipe::with('recipeItems')->findOrFail($recipeId);
        $pluckRecipeItemCoefficient = $recipe->recipeItems->pluck('coefficient', 'id');
        $pluckRecipeItemProduct = $recipe->recipeItems->pluck('product', 'id');


        // Plan item status
        $planItemStatus = self::getPlanItemNewStatus();

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

        return $newProductionPlan;
    }

    public function findOne(int $id): array
    {
        $data = ProductionPlan::with([
            'planItems',
            'recipe',
            'amountType',
            'status'
        ])->findOrFail($id);

        $statuses = Status::whereIn('code', [
            'productionPlanDepartNew',
            'productionPlanDepartReceived',
            'productionPlanDepartCompleted',
        ])->get();

        $groupedPlanItems = $data->planItems->groupBy(fn($item) => $item->status->code ?? 'unknown');
        $result = $statuses->map(function ($status) use ($groupedPlanItems) {
            return [
                "id" => $status->id,
                "name" => $status->name,
                "code" => $status->code,
                "items" => $groupedPlanItems[$status->code] ?? [], // Attach items or empty array
            ];
        });

        $data->plan_items_by_statuses = $result;

        return [
            'data' => $data
        ];
    }

    public function findPlanItemsByDepart(array $queryParams): Collection
    {
        $query =  ProductionPlanItem::with([
            'plan',
            'depart',
            'recipeItem',
            'departStatus',
            'status',
            'product',
            'amountType'
        ]);

        foreach ($queryParams as $key => $item) {

            if ($key === 'status_code') {
                $status = StatusService::findByCode($item);
                $query->where('status_id', $status->id);
            } else {
                $query->where($key, $item);
            }
        }

        return $query->latest()
            ->get();
    }

    public function receivePlanItem(int $planItemId): ProductionPlanItem
    {
        $statusPlanProcess = self::getProcessStatus();
        $statusPlanItemReceived = self::getPlanItemReceivedStatus();

        DB::beginTransaction();

        try {
            // Plan Item
            $planItem = ProductionPlanItem::findOrFail($planItemId);
            $planItem->update(['status_id' => $statusPlanItemReceived->id]);

            // Plan
            ProductionPlan::findOrFail($planItem->plan_id)->update(['status_id' => $statusPlanProcess->id]);

            DB::commit();

            return $planItem;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function completedPlanItem(int $planItemId): array
    {
        $statusReceived = self::getPlanItemCompletedStatus();
        ProductionPlanItem::where('id', $planItemId)
            ->firstOrFail()
            ->update(['status_id' => $statusReceived->id]);

        return [
            'message' => "Topshiriq muvaffaqiyatli bajarildi",
        ];
    }

    public function finish(int $planId): ProductionPlan
    {
        $statusCompleted = self::getCompletedStatus();
        $statusPlanItem = self::getPlanItemCompletedStatus();

        DB::beginTransaction();

        try {
            $plan = ProductionPlan::where('id', $planId)
                ->where('status_id', '!=', $statusCompleted->id)
                ->firstOrFail();

            $plan->update([
                'status_id' => $statusCompleted->id,
                'finish_date' => now()
            ]);

            $plan->planItems()->update(['status_id' => $statusPlanItem->id]);

            DB::commit();

            return $plan;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new InternalErrorException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public static function getNewStatus()
    {
        return StatusService::findByCode('productionPlanNew');
    }

    public static function getProcessStatus()
    {
        return StatusService::findByCode('productionPlanProcess');
    }

    public static function getCancelStatus()
    {
        return StatusService::findByCode('productionPlanCancel');
    }

    public static function getCompletedStatus()
    {
        return StatusService::findByCode('productionPlanCompleted');
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

    public function sendNotificationsToDepartments(ProductionPlan $newProductionPlan, array $departsIds): void
    {
        $departmentUsers = User::whereHas('departs', function ($query) use ($departsIds) {
            $query->whereIn('depart_id', $departsIds);
        })->get();

        if ($departmentUsers->isNotEmpty()) {
            $producer = User::findOrFail(Auth::id());

            broadcast(new ProductionPlanCreated($newProductionPlan, $producer, $departmentUsers));
        }
    }
}
