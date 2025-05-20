<?php

use App\Http\Controllers\Department\TransPermController;
use Illuminate\Support\Facades\Route;

Route::apiResource('departments/transfer-permissions', TransPermController::class);
Route::get('departments/transfer-permissions/code/{code}', [TransPermController::class, 'showByCode'])
  ->middleware(['auth:sanctum']);
