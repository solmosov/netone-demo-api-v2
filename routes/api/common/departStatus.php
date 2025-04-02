<?php

use App\Http\Controllers\DepartStatusController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/departments/statuses', DepartStatusController::class)->middleware('auth:sanctum');
