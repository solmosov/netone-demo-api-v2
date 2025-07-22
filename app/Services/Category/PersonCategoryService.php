<?php

namespace App\Services\Category;

use App\Models\PersonCategory;

class PersonCategoryService
{
  public function __construct(
    protected PersonCategory $personCategory
  ) {}

  public function findAll()
  {
    return $this->personCategory->all();
  }
  public function findOne($id)
  {
    return $this->personCategory->findOrFail($id);
  }
  public function create(array $data): PersonCategory
  {
    return $this->personCategory->create($data);
  }
  public function update(array $data, $id)
  {
    $personToUpdate = $this->findById($id);

    return $personToUpdate->update($data);
  }
  public function delete($id)
  {
    $personToDelete = $this->findById($id);

    $personToDelete->delete();

    return $personToDelete;
  }


  private function findById($id)
  {
    return $this->personCategory->findOrFail($id);
  }
}
