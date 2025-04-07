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
    $data = Depart::with('user')->get();

    return [
      'data' => $data,
    ];
  }


  public function findOne(int $id): array
  {
    $data = Depart::with(['user', 'transPerms.departs', 'departStatuses'])->findOrFail($id);

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
      $polka->update(['owner_id', $newDepart->id]);

      DB::commit();
      
      return [
        'message' => "Yangi bo'lim qo'shildi",
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
