<?php

use App\Http\Controllers\ProducerDepartProductTransController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'producer-product-transfers/depart', 'middleware' => ['auth:sanctum']], function () {
  Route::get('/', [ProducerDepartProductTransController::class, 'index']);
  Route::get('/{producer_product_trans}', [ProducerDepartProductTransController::class, 'show']);
  Route::post('/', [ProducerDepartProductTransController::class, 'sendProductToProducer']);
});