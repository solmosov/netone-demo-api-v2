<?php

namespace App\Services\Department;

use App\Models\Department\DepartStatus;

class DepartStatusService
{
  public function findAll()
  {
    $data = DepartStatus::get();

    return [
      'data' => $data
    ];
  }
  public function findOne(int $id)
  {
    $data = DepartStatus::findOrFail($id);

    return [
      'data' => $data
    ];
  }
  public function create(array $data)
  {
    // Create
    $newData = DepartStatus::create($data);
    // Finish
    return [
      'message' => "Yangi $newData->name status qo'shildi",
      'data' => $newData
    ];
  }
  public function update(array $data, int $id)
  {
    $departStatus = DepartStatus::findOrFail($id);

    $departStatus->update($data);

    return [
      'message' => "Bo'lim status yangilandi",
      'data' => $departStatus,
    ];
  }
  public function delete(int $id)
  {
    $departStatus = DepartStatus::findOrFail($id);
    $departStatus->deleteOrFail();

    return [
      'message' => "Bo'lim status o'chirildi"
    ];
  }
}
