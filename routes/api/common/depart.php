<?php

use App\Http\Controllers\Department\DepartController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'departments', 'middleware' => ['auth:sanctum']], function () {
  Route::apiResource('/', DepartController::class);
  Route::get('by-trans-perms', [DepartController::class, 'indexByTransPerm']);
  Route::post('assign-permissions', [DepartController::class, 'assignPerm']);
});
