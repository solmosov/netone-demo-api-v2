<?php

use App\Http\Controllers\Department\TransPermController;
use Illuminate\Support\Facades\Route;

Route::apiResource('departments/transfer-permissions', TransPermController::class);
