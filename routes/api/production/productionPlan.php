<?php

use App\Http\Controllers\ProductionPlanController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/production-plan/items', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/', [ProductionPlanController::class, 'showPlanItemsByDepart']);
    Route::put('/{plan_item_id}/receive', [ProductionPlanController::class, 'receivePlanItem']);
    Route::put('/{plan_item_id}/completed', [ProductionPlanController::class, 'completedPlanItem']);
});
Route::apiResource('/production-plan', ProductionPlanController::class)->middleware(['auth:sanctum']);
