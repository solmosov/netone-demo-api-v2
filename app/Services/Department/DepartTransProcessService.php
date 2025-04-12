<?php

namespace App\Services\Department;

use App\Exceptions\ServerErrorException;
use App\Models\Depart;
use App\Models\DepartTransProcess;
use App\Models\Status;
use App\Services\Status\StatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepartTransProcessService
{
  public static function getSendProductStatus(): Status
  {
    return StatusService::findByCode('departSendProduct');
  }
  public static function getReceiveProductStatus(): Status
  {
    return StatusService::findByCode('departSendProduct');
  }
  public static function getCancelProductStatus(): Status
  {
    return StatusService::findByCode('departCancelProduct');
  }

  public function create(array $data)
  {
    // Req Data
    $transPermId = $data['trans_perm_id'];
    $senderDepartId = $data['sender_depart_id'];
    $receiverDepartId = $data['receiver_depart_id'];
    $productList = $data['product_list'];
    $comment = $data['comment'] ?? null;

    // Receiver Depart
    $receiverDepart = Depart::findOrFail($receiverDepartId);

    DB::beginTransaction();

    try {
      // New Process
      $newProcess = DepartTransProcess::create([
        'trans_perm_id' => $transPermId,
        'sender_depart_id' => $senderDepartId,
        'receiver_depart_id' => $receiverDepartId,
        'sender_user_id' => Auth::id(),
        'status_id' => DepartTransProcessService::getSendProductStatus()->id,
        'comment' => $comment,
      ]);
      
      $insertDetailData = [];
      foreach ($productList as $item) {
        $insertDetailData[] = [
          'depart_trans_process_id' => $newProcess->id,
          'product_id' => $item['product_id'],
          'polka_id' => $item['polka_id'],
          'amount' => $item['amount'],
          'amount_type_id' => $item['amount_type_id']
        ];
      }
      $newProcess->details()->createMany($insertDetailData);

      // Finish
      DB::commit();

      return [
        'message' => "{$receiverDepart->name} bo'limga mahsulotlar yuborildi, qabul qilinishi kutilmoqda"
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      throw new ServerErrorException($e->getMessage(), (int) $e->getCode(), $e);
    }
  }
}
