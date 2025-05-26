<?php

namespace App\Services\Expense;

use App\Models\Expense;

class ExpenseService
{
  public function findAll(array $queryParams)
  {
    // Query Params
    $isTree = $queryParams['is_tree'] ?? null;

    $query = Expense::query();
    if ($isTree) {
      $query->with('children')
        ->where('parent_id', null);
    }

    return $query->with('parent')
      ->get();
  }
  public function findOne(int $id)
  {
    return Expense::with(['children', 'parent'])->findOrFail($id);
  }
  public function create() {}
  public function update() {}
  public function delete() {}
}
