<?php

use App\Http\Controllers\DepartStockController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/departments/stocks', DepartStockController::class)->middleware(['auth:sanctum']);
