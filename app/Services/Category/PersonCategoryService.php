<?php

namespace App\Services\Category;

use App\Models\PersonCategory;
use Illuminate\Database\Eloquent\Collection;

class PersonCategoryService
{
  public function __construct(
    protected PersonCategory $personCategory
  ) {}

  public function findAll(): Collection
  {
    return $this->personCategory->all();
  }
  public function findOne($id): PersonCategory
  {
    return $this->personCategory->findOrFail($id);
  }
  public function create(array $data): PersonCategory
  {
    return $this->personCategory->create($data);
  }
  public function update(array $data, $id): PersonCategory
  {
    $personToUpdate = $this->findById($id);

    $personToUpdate->update($data);


    return $personToUpdate;
  }
  public function delete($id): PersonCategory
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
