<?php

namespace App\Services\Department;

use App\Exceptions\ServerErrorException;
use App\Models\DepartTransPermProperty;
use Illuminate\Support\Facades\DB;

class DepartTransPermService
{
  public function findAll() {}
  public function findOne() {}
  public function create(array $data)
  {
    $ownerDepartId = $data['owner_depart_id'];
    $permId = $data['perm_id'];
    $departList = $data['depart_list'];


    DB::beginTransaction();

    try {
      $newProperty = DepartTransPermProperty::create([
        'trans_perm_id' => $permId,
        'depart_id' => $ownerDepartId,
      ]);

      $attachDetailsList = [];
      foreach ($departList as $departId) {
        $attachDetailsList[] = [
          'property_id' => $newProperty->id,
          'depart_id' => $departId,
        ];
      }
      $newProperty->details()->createMany($attachDetailsList);

      DB::commit();
      return [
        'message' => "Yangi ruxsat muvaffaqiyatli qo'shildi"
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      throw new ServerErrorException($e->getMessage(), (int)$e->getCode(), $e);
    }
  }
  public function update() {}
  public function delete() {}
}
