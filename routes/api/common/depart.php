<?php

use App\Http\Controllers\Department\DepartController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'departments', 'middleware' => ['auth:sanctum']], function () {
  Route::get('/', [DepartController::class, 'index']);
  Route::get('by-trans-perms', [DepartController::class, 'indexByTransPerm']);
  Route::get('/{department}', [DepartController::class, 'show']);
  Route::post('/', [DepartController::class, 'store']);
  Route::post('assign-permissions', [DepartController::class, 'assignPerm']);
  Route::post('assign-users', [DepartController::class, 'assignNewUser']);
  Route::put('/{department}', [DepartController::class, 'update']);
  Route::delete('assign-users/remove', [DepartController::class, 'removeAssignUser']);
  Route::delete('/{department}', [DepartController::class, 'destroy']);
});
