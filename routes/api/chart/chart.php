<?php

use App\Http\Controllers\Chart\ChartController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'charts'], function () {
  Route::get('sales-expense-profit', [ChartController::class, 'salesExpenseProfit']);
});
