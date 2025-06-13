<?php

namespace App\Services\Category;

use App\Models\Category;

class CategoryService
{
  public function findAll(array $queryParams)
  {
    // Params
    $isTree = $queryParams['is_tree'] ?? null;

    $query = Category::with('parent');

    if ($isTree) {
      $query->with('children')
        ->whereNull('parent_id');
    }

    $data = $query->get();

    return [
      'data' => $data
    ];
  }
  public function findOne(int $id)
  {
    return Category::with(['children', 'parent'])->findOrFail($id);
  }
  public function create(array $data)
  {
    return Category::create($data);
  }
  public function update(array $data, int $id)
  {
    $category = $this->findOne($id);

    $category->update($data);
    return  $category;
  }
  public function delete(int $id)
  {
    $category = $this->findOne($id);

    $category->delete();

    return $category;
  }
}
