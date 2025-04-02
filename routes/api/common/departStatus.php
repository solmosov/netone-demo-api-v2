<?php

use App\Http\Controllers\DepartStatusController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/departments/status', DepartStatusController::class)->middleware('auth:sanctum');
