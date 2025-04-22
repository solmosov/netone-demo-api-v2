<?php

namespace App\Services\Department;

use App\Models\DepartStock;
use App\Models\Product;

class DepartStockService
{
  public function create(array $data): array
  {
    // Req Data
    $departId = $data['depart_id'];
    $polkaId = $data['polka_id'];
    $productId = $data['product_id'];

    // Product
    $product = Product::findOrFail($productId);
    $amountTypeId = $product->price_amount_type_id;

    // New Depart Stock
    $newDepartStock = DepartStock::create([
      'depart_id' => $departId,
      'polka_id' => $polkaId,
      'product_id' => $productId,
      'amount_type_id' => $amountTypeId,
    ]);

    return [
      'message' => "Yangi bo'lim zaxira qo'shildi",
      'data' => $newDepartStock
    ];
  }
}
