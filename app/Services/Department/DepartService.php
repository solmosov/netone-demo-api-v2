<?php

namespace App\Services\Department;

use App\Exceptions\ServerErrorException;
use App\Models\Depart;
use App\Models\DepartTransPermProperty;
use App\Models\Polka;
use Illuminate\Support\Facades\DB;

class DepartService
{
  public function findAll(): array
  {
    $data = Depart::with(['users', 'polka'])->get();

    return [
      'data' => $data,
    ];
  }


  public function findOne(int $id): array
  {
    $data = Depart::with(['users', 'polka'])->findOrFail($id);

    return [
      'data' => $data
    ];
  }


  public function create(array $data)
  {
    // Req Data
    $userList = $data['user_list'];
    $polkaId = $data['polka_id'];
    $name = $data['name'];
    $comment = $data['comment'] ?? null;

    // Polka
    $polka = Polka::where('id', $polkaId)
      ->whereNull('owner_id')
      ->firstOrFail();

    DB::beginTransaction();
    try {
      $newDepart = Depart::create([
        'name' => $name,
        'comment' => $comment,
      ]);

      // Attach User
      $newDepart->users()->attach($userList);

      // Polka
      $polka->owner_id = $newDepart->id;
      $polka->save();

      DB::commit();

      return [
        'message' => "Yangi bo'lim qo'shildi",
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      throw new ServerErrorException($e->getMessage(), (int)$e->getCode(), $e);
    }
  }

  public function assignDepart(array $data)
  {
    // Req Data
    $permId = $data['perm_id'];
    $ownerDepartId = $data['owner_depart_id'];
    $departList = $data['depart_list'];

    // Owner Depart
    $ownerDepart = Depart::findOrFail($ownerDepartId);

    // Create Property
    $newProperty = DepartTransPermProperty::create([
      'depart_id' => $ownerDepart->id,
      'trans_perm_id' => $permId
    ]);

    // Create Property Details
    $transPermDetailsList = [];
    foreach ($departList as $item) {
      $transPermDetailsList[] = [
        'property_id' => $newProperty->id,
        'depart_id' => $item->id,
      ];
    }
    $newProperty->details()->createMany($transPermDetailsList);

    // Finish
    

  }

  public function update(array $data, int $id): array
  {
    // Req Data
    $userList = $data['user_list'] ?? [];
    $polkaId = $data['polka_id'] ?? null;

    DB::beginTransaction();

    try {
      $depart = Depart::with('users')->findOrFail($id);
      $depart->update($data);

      // User
      if (!empty($userList)) {
        $depart->users()->detach();
        $depart->users()->attach($userList);
      }

      // Polka
      if ($polkaId) {
        $polka = Polka::whereNull('owner_id')
          ->whereNull('owner_id')
          ->findOrFail($id);

        $polka->owner_id = $depart->id;
        $polka->save();
      }

      DB::commit();

      return [
        'message' => "$depart->name yangilandi",
        'data' => $depart
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      throw new ServerErrorException($e->getMessage(), (int) $e->getCode(), $e);
    }
  }

  public function delete(int $id): array
  {
    $depart = Depart::with('users')->findOrFail($id);

    return [
      'message' => "$depart->name o'chirildi",
      'data' => $depart
    ];
  }
}
