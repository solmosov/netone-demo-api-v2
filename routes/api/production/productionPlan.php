<?php

use App\Http\Controllers\ProductionPlanController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/production-plan', ProductionPlanController::class);
