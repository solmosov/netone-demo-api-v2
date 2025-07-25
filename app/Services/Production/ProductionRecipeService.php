<?php

namespace App\Services\Production;

use App\Exceptions\ServerErrorException;
use App\Models\ProductionRecipe;
use App\Models\RecipeItems;
use Illuminate\Support\Facades\DB;

class ProductionRecipeService
{
  public function findAll()
  {
    return ProductionRecipe::with(
      'outProduct',
      'outAmountType',
      'recipeItems.product'
    )
      ->latest()
      ->get();
  }
  public function findOne(int $id)
  {
    return ProductionRecipe::with(
      'outProduct',
      'outAmountType',
      'recipeItems.product'
    )->findOrFail($id);
  }
  public function create(array $data)
  {
    // Req Data
    // $name = $data['name'];
    // $out_product_id = $data['out_product_id'];
    // $out_amount_type_id = $data['out_amount_type_id'];
    // $out_amount = $data['out_amount'];
    // $items_list = $data['items_list'];

    DB::beginTransaction();

    try {

      $newRecipe = $this->createWithoutTransaction($data);

      DB::commit();

      return $newRecipe;
    } catch (\Exception $e) {
      DB::rollBack();
      throw new ServerErrorException($e->getMessage(), (int) $e->getCode(), $e);
    }
  }
  public function update(array $data, int $id)
  {
    // Req Data
    $name = $data['name'];
    $out_product_id = $data['out_product_id'];
    $out_amount_type_id = $data['out_amount_type_id'];
    $out_amount = $data['out_amount'];
    $items_list = $data['items_list'];

    // Find
    $recipe = ProductionRecipe::findOrFail($id);


    DB::beginTransaction();

    try {

      // New Recipe
      $recipe->update([
        "name" => $name,
        'out_product_id' => $out_product_id,
        'out_amount_type_id' => $out_amount_type_id,
        'out_amount' => $out_amount
      ]);


      // Create Recipe Items
      $existingIds = [];
      foreach ($items_list as $item) {
        $itemId = $item['item_id'] ?? null;
        $product_id = $item['product_id'];
        $amount = $item['amount'];
        $amount_type_id = $item['amount_type_id'];
        $is_change = (bool) $item['is_change'];


        // Coefficient
        $coefficient = $amount / $out_amount;

        // Item Data
        $itemData = [
          'production_recipe_id' => $recipe->id,
          "product_id" => $product_id,
          "amount" => $amount,
          "amount_type_id" => $amount_type_id,
          'coefficient' => $coefficient,
          'is_change' => $is_change,
        ];

        if ($itemId) {
          // Update
          $existingIds[] = $itemId;
          RecipeItems::findOrFail($itemId)->update($itemData);
        } else {
          // Create
          $newRecipeItem = RecipeItems::create($itemData);
          $existingIds[] = $newRecipeItem->id;
        }
      }

      $recipe->recipeItems()->whereNotIn('id', $existingIds)->delete();

      DB::commit();

      return $recipe;
    } catch (\Exception $e) {
      DB::rollBack();
      throw new ServerErrorException($e->getMessage(), (int) $e->getCode(), $e);
    }
  }
  public function updateItem(array $data, int $id)
  {
    // Req Body
    $productId = $data['product_id'] ?? null;
    $amountTypeId = $data['amount_type_id'] ?? null;
    $isChange = $data['is_change'] ?? null;

    // Recipe Item
    $recipeItem = RecipeItems::findOrFail($id);

    if ($productId) $recipeItem->product_id = $productId;
    if ($amountTypeId) $recipeItem->amount_type_id = $amountTypeId;
    if ($isChange !== null) $recipeItem->isChange = $isChange;

    $recipeItem->save();

    return $recipeItem;
  }

  public function delete(int $id)
  {
    // Recipe
    $recipe = ProductionRecipe::findOrFail($id);

    // Delete 
    $recipe->delete();

    return $recipe;
  }

  // --- HELPER METHODS ---
  public function createWithoutTransaction(array $data): ProductionRecipe
  {
    // Req Data
    $name = $data['name'];
    $out_product_id = $data['out_product_id'];
    $out_amount_type_id = $data['out_amount_type_id'];
    $out_amount = $data['out_amount'];
    $items_list = $data['items_list']; // [product_id, amount, amount_type_id, is_change]

    // New Recipe
    $newRecipe = ProductionRecipe::create([
      "name" => $name,
      'out_product_id' => $out_product_id,
      'out_amount_type_id' => $out_amount_type_id,
      'out_amount' => $out_amount
    ]);


    // Create Recipe Items
    $list = [];
    foreach ($items_list as $item) {
      $product_id = $item['product_id'];
      $amount = $item['amount'];
      $amount_type_id = $item['amount_type_id'];
      $is_change = (bool) $item['is_change'];

      $list[] = [
        'production_recipe_id' => $newRecipe->id,
        "product_id" => $product_id,
        "amount" => $amount,
        "amount_type_id" => $amount_type_id,
        'coefficient' => $amount / $out_amount,
        'is_change' => $is_change,
      ];
    }
    $newRecipe->recipeItems()->createMany($list);


    // Load items
    $newRecipe->load('recipeItems');

    // Finish
    return $newRecipe;
  }
}
