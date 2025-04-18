<?php

use App\Http\Controllers\ProductionPlanController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/production-plan', ProductionPlanController::class)->middleware(['auth:sanctum']);
Route::group(['prefix' => '/production-plan/item/{plan_item_id}', 'middleware' => ['auth:sanctum']], function () {
    Route::put('/receive', [ProductionPlanController::class, 'receivePlanItem']);
    Route::put('/completed', [ProductionPlanController::class, 'completedPlanItem']);
});
