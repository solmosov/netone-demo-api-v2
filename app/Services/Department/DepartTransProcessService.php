<?php

namespace App\Services\Department;

use App\Exceptions\InvalidDataException;
use App\Exceptions\ServerErrorException;
use App\Models\Depart;
use App\Models\Department\DepartTransProcessDetail;
use App\Models\DepartTransProcess;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Status;
use App\Services\Status\StatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepartTransProcessService
{
  public function findAll()
  {
    $data = DepartTransProcess::with([
      'transferPermission',
      'senderDepartment',
      'receiverDepartment',
      'senderUser',
      'receiverUser',
      'status'
    ])
      ->orderByDesc('created_at')
      ->get();

    return [
      'data' => $data
    ];
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

  public function findOne(int $id)
  {
    $data = DepartTransProcess::with([
      'transferPermission',
      'senderDepartment',
      'receiverDepartment',
      'senderUser',
      'receiverUser',
      'status',
      'details'
    ])
      ->findOrFail($id);

    return [
      'data' => $data
    ];
  }

  public function receive(array $data, int $id)
  {
    // Req Data
    $reqDetailsList = $data['details_list'];

    // Send Status
    $sendStatus = DepartTransProcessService::getSendProductStatus();

    // Trans Process
    $transProcess = DepartTransProcess::with('details')
      ->where('status_id', $sendStatus->id)
      ->findOrFail($id);

    // Receiver Depart Id
    $receiverDepartId = $transProcess->receiver_depart_id;

    $exists = DB::table('depart_user')
      ->where('depart_id', $receiverDepartId)
      ->where('user_id', Auth::id());
    if ($exists) {
      throw new InvalidDataException("Bu amalni bajarib bo'lmadi", 403);
    }

    // Status
    $receivedStatus = DepartTransProcessService::getReceiveProductStatus();

    DB::beginTransaction();
    try {
      // Increment Stock
      foreach ($reqDetailsList as $reqItem) {
        $reqItemId = $reqItem['item_id'];
        $reqPolkaId = $reqItem['polka_id'];

        // Transfer Item
        $transProcessItem = DepartTransProcessDetail::where('depart_trans_process_id', $receiverDepartId)
          ->findOrFail($reqItemId);

        // Change Stock
        $stockItem = ProductStock::where('depart_id', $receiverDepartId)
          ->where('amount_type_id', $transProcess->amount_type_id)
          ->findOrFail($reqPolkaId);

        // Product
        $stockItem->decrement('amount', $transProcessItem->amount);


        // Set Received Polka
        $transProcessItem->received_polka_id = $reqPolkaId;
        $transProcessItem->save();
      }

      // Change Trans Process Status
      $transProcess->status_id = $receivedStatus->id;
      $transProcess->save();

      // Finish
      dd('render');

      return [
        'message' => "Yuk muvaffaqiyatli qabul qilindi, zaxirani tekshiring"
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      throw new ServerErrorException($e->getMessage(), (int)$e->getCode(), $e);
    }
  }

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
}
