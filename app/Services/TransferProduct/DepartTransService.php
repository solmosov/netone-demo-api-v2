<?php

namespace App\Services\TransferProduct;

use App\Exceptions\ServerErrorException;
use App\Models\Depart;
use App\Models\DepartTrans;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepartTransService
{
  public function findAll() {}
  public function findOne() {}
  public function create(array $data)
  {
    $userId = Auth::id();
    $transPermId = $data['trans_perm_id'];
    $fromDepartId = $data['from_depart_id'];
    $toDepartId = $data['to_depart_id'];
    $productList = $data['product_list'];

    // To Depart
    $toDepart = Depart::findOrFail($toDepartId);

    DB::beginTransaction();

    try {
      // New Transfer
      $newData = DepartTrans::create([
        'user_id' => $userId,
        'trans_perm_id' => $transPermId,
        'from_depart_id' => $fromDepartId,
        "to_depart_id" => $toDepartId,
      ]);

      $detailsList = [];

      foreach ($productList as $item) {
        $detailsList[] = [
          'depart_trans_id' => $newData->id,
          'product_id' => $item['product_id'],
          'amount' => $item['amount'],
          'amount_type_id' => $item['amount_type_id'],
        ];
      }

      $newData->details()->createMany($detailsList);

      DB::commit();
      
      return [
        "message" => "$toDepart->name bo'limga yangi yuk o'tkazma bajarildi",
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      throw new ServerErrorException($e->getMessage(), (int)$e->getCode(), $e);
    }
  }
}
