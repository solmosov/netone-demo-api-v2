<?php

namespace App\Services\Production;

use App\Models\ProductionPlan;
use App\Services\Status\StatusService;

class ProductionPlanService
{

  public function create(array $data)
  {
    // Req Data
    $recipeId = $data['recipe_id'];
    $name = $data['name'];
    $amount = $data['amount'];
    $amountTypeId = $data['amount_type_id'];
    $comment = $data['comment'] ?? null;

    // New Production Plan
    $newProductionPlan = ProductionPlan::create([
      'recipe_id' => $recipeId,
      'amount_type_id' => $amountTypeId,
      'status_id' => self::getNewStatus()->id,
      'name' => $name,
      'amount' => $amount,
      'comment' => $comment
    ]);

    return [
      'message' => "Yangi `$newProductionPlan->name` qo'shildi"
    ];
  }

  public static function getNewStatus()
  {
    return StatusService::findByCode('productionPlanNew');
  }
}
