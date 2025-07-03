<?php

use App\Http\Controllers\Chart\ChartController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'charts','middleware' => 'auth:sanctum'], function () {
    Route::get('sales-expense-profit', [ChartController::class, 'salesExpenseProfit']);
    Route::get('expenses', [ChartController::class, 'expense']);
});
