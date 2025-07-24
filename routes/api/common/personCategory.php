<?php

use App\Http\Controllers\PersonCategoryController;
use Illuminate\Support\Facades\Route;

Route::apiResource('person-categories', PersonCategoryController::class)->middleware('auth:sanctum');
