<?php

namespace App\Services\TransferProduct;

use App\Models\DepartTrans;
use Illuminate\Support\Facades\Auth;

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

    // New Transfer
    $newData = DepartTrans::create([
      'user_id' => $userId,
      'trans_perm_id' => $transPermId,
      'from_depart_id' => $fromDepartId,
      "to_depart_id" => $toDepartId,
    ]);

    // 


    return [
      "message" => "",
      "data" => ""
    ];
  }
}
