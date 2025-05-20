<?php

use App\Http\Controllers\ProductionRecipeController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/production-recipes', ['middleware' => ['auth:sanctum']]], function () {
  Route::group(['prefix' => 'items', 'middleware' => ['auth:sanctum']], function () {
    Route::put('/{recipe_item}', [ProductionRecipeController::class, 'updateItem']);
  });

  Route::get('/', [ProductionRecipeController::class, 'index']);
  Route::get('/{recipe}', [ProductionRecipeController::class, 'show']);
  Route::post('/', [ProductionRecipeController::class, 'create']);
  Route::put('/{recipe}', [ProductionRecipeController::class, 'update']);
  Route::delete('/{recipe}', [ProductionRecipeController::class, 'destroy']);
});
