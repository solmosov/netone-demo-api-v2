<?php

use App\Http\Controllers\DepartTransProcessController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/departments/transfer-process', 'middleware' => ['auth:sanctum']], function () {
  Route::get('/', [DepartTransProcessController::class, 'index']);
  Route::get('/{id}', [DepartTransProcessController::class, 'show']);
  Route::post('/', [DepartTransProcessController::class, 'store']);
});
