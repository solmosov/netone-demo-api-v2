<?php

namespace App\Services\Product;

use App\Exceptions\ServerErrorException;
use App\Models\Product;

class ProductService
{
  public function findAll()
  {
    $data = Product::with('priceAmountType')->latest()->get();

    return [
      'data' => $data,
    ];
  }
  public function findOne(int $id)
  {
    return Product::findOrFail($id);
  }
  public function create(array $data)
  {
    // Req Body
    $categoryId = $data['category_id'] ?? null;
    $name = $data['name'];
    $sale_price = $data['sale_price'];
    $price_amount_type_id = $data['price_amount_type_id'];
    $is_change_price = $data['is_change_price'];

    return Product::create([
      'category_id' => $categoryId,
      'name' => $name,
      'cost_price' => 0,
      'sale_price' => $sale_price,
      'price_amount_type_id' => $price_amount_type_id,
      'is_changE_price' => $is_change_price
    ]);
  }
  public function update(array $data, int $id)
  {
    $product = Product::findOrFail($id);

    try {
      $product->update($data);

      return $product;
    } catch (\Exception $e) {
      throw new ServerErrorException($e->getMessage(), (int) $e->getCode(), $e);
    }
  }
  public function delete(int $id)
  {
    $product = Product::findOrFail($id);

    try {
      $product->delete();

      return $product;
    } catch (\Exception $e) {
      throw new ServerErrorException($e->getMessage(), (int) $e->getCode(), $e);
    }
  }
}
