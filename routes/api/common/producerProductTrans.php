<?php

use App\Http\Controllers\ProducerProductTransController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'producer-product-transfers/producer', 'middleware' => ['auth:sanctum']], function () {
  Route::get('/', [ProducerProductTransController::class, 'index']);
  Route::get('/{producer_product_trans}', [ProducerProductTransController::class, 'index']);
  Route::post('/', [ProducerProductTransController::class, 'sendProductToDepart']);
  Route::post('/{producer_product_trans}/receive', [ProducerProductTransController::class, 'receiveProductFromDepart']);
});
