<?php

use App\Http\Controllers\Department\DepartPermissionController;
use Illuminate\Support\Facades\Route;

Route::apiResource('departments/transfer-permissions', DepartPermissionController::class);