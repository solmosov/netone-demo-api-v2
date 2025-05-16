<?php

use App\Http\Controllers\ProducerProductTransController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'producer-product-transfers/producer', 'middleware' => ['auth:sanctum']], function () {
  Route::get('/receives', [ProducerProductTransController::class, 'indexReceive']);
  Route::post('/{producer_product_trans}/receives', [ProducerProductTransController::class, 'receiveProductFromDepart']);
  
  Route::get('/', [ProducerProductTransController::class, 'index']);
  Route::get('/{producer_product_trans}', [ProducerProductTransController::class, 'show']);
  Route::post('/', [ProducerProductTransController::class, 'sendProductToDepart']);
});
