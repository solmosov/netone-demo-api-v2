<?php

namespace App\Services\Department;

use App\Exceptions\ServerErrorException;
use App\Models\Depart;
use App\Models\DepartTransPermProperty;
use App\Models\DepartTransPermPropertyDetails;
use Illuminate\Support\Facades\DB;

class DepartService
{
  public function findAll(): array
  {
    $data = Depart::with('user')->get();

    return [
      'data' => $data,
    ];
  }


  public function findOne(int $id): array
  {
    $data = Depart::with('user')->findOrFail($id);

    return [
      'data' => $data
    ];
  }


  public function create(array $data)
  {
    // Req Data
    $userId = $data['user_id'];
    $name = $data['name'];
    $comment = $data['comment'] ?? null;
    $permList = $data['perm_list'];

    DB::beginTransaction();

    try {
      // Create
      $newDepart = Depart::create([
        'user_id' => $userId,
        'name' => $name,
        'comment' => $comment
      ]);



      foreach ($permList as $perm) {
        $permId = $perm['perm_id'];
        $departList = $perm['depart_list'];


        $newDepartTransProperty  = DepartTransPermProperty::create([
          'trans_perm_id' => $permId,
          'depart_id' => $newDepart->id,
        ]);


        $detailsList = [];
        foreach ($departList as $departId) {
          $detailsList[] = [
            'depart_id' => $departId,
            'property_id' => $newDepartTransProperty->id,
          ];
        }

        $newDepartTransProperty->details()->createMany($detailsList);
      }

      DB::commit();
      return [
        'message' => "Yangi $newDepart->name bo'lim muvaffaqiyatli yaratildi",
        'data' => $newDepart
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      throw new ServerErrorException($e->getMessage(), (int)$e->getCode(), $e);
    }
  }

  public function update(array $data, int $id): array
  {

    $depart = Depart::with('user')->findOrFail($id);

    $depart->update($data);

    return [
      'message' => "$depart->name yangilandi",
      'data' => $depart
    ];
  }

  public function delete(int $id): array
  {
    $depart = Depart::with('user')->findOrFail($id);

    return [
      'message' => "$depart->name o'chirildi",
      'data' => $depart
    ];
  }
}
