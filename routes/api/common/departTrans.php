<?php

use App\Http\Controllers\DepartTransController;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "/departments/product-transfer", "middleware" => ['auth:sanctum']], function () {
  Route::post("/", [DepartTransController::class, 'store']);
});
