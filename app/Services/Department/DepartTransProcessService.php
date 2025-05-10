<?php

namespace App\Services\Department;

use App\Exceptions\InvalidDataException;
use App\Exceptions\ServerErrorException;
use App\Models\Department\DepartTransProcessDetail;
use App\Models\DepartStock;
use App\Models\DepartTransProcess;
use App\Models\Status;
use App\Models\TransPerm;
use App\Services\Status\StatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepartTransProcessService
{
  public function findAll(array $queryParams): array
  {
    // Auth User
    $authUser = Auth::user();

    // Query
    $query = $query = $this->createQuery($queryParams);

    if (!$authUser->isAdmin()) {
      $query
        ->where('sender_user_id', $authUser->id)
        ->whereOr('receiver_user_id', $authUser->id);
    }

    $data = $query
      ->orderByDesc('created_at')
      ->limit(100)
      ->get();

    return [
      'data' => $data
    ];
  }

  public function findAllBySend(array $queryParams): array
  {
    // Auth User
    $authUser = Auth::user();

    // Query
    $query = $query = $this->createQuery($queryParams);

    if (!$authUser->isAdmin()) {
      $query->where('sender_user_id', $authUser->id);
    }

    $data = $query
      ->orderByDesc('created_at')
      ->limit(100)
      ->get();

    return [
      'data' => $data
    ];
  }

  public function findAllByReceive(array $queryParams): array
  {
    // Auth User
    $authUser = Auth::user();

    // Query
    $query = $query = $this->createQuery($queryParams);

    if (!$authUser->isAdmin()) {
      $query->where('receiver_user_id', $authUser->id);
    }

    $data = $query
      ->orderByDesc('created_at')
      ->limit(100)
      ->get();

    return [
      'data' => $data
    ];
  }

  public function createQuery(array $queryParams)
  {
    // Query
    $query = DepartTransProcess::with([
      'transferPermission',
      'senderDepartment',
      'receiverDepartment',
      'senderUser',
      'receiverUser',
      'status'
    ]);

    foreach ($queryParams as $key => $value) {
      if ($key == 'trans_perm_code') {
        $transPerm = TransPerm::where('code', $value)->firstOrFail();
        $query->where('trans_perm_id', $transPerm->id);
      } else if ($key == 'status_code') {
        $status = StatusService::findByCode($value);
        $query->where('status_id', $status->id);
      } else if (!is_null($value)) {
        $query->where($key, $value);
      }
    }

    return $query;
  }

  public function create(array $data)
  {
    $authId = Auth::id();

    // Request Data
    $senderDepartId = $data['sender_depart_id'];
    $receiverDepartId = $data['receiver_depart_id'];
    $productList = $data['product_list'];
    $comment = $data['comment'] ?? null;

    // Trans Perm
    $sendTransPerm = DepartTransPermService::getSendProduct();

    // Status Process 
    $statusProcess = self::getSendProductStatus();

    DB::beginTransaction();

    try {
      // New Process
      $newProcess = DepartTransProcess::create([
        'trans_perm_id' => $sendTransPerm->id,
        'sender_depart_id' => $senderDepartId,
        'receiver_depart_id' => $receiverDepartId,
        'sender_user_id' => $authId,
        'status_id' => $statusProcess->id,
        'comment' => $comment,
      ]);

      // New Process Details
      $creatableDetails = [];

      foreach ($productList as $item) {
        // Values
        $departStockId = $item['depart_stock_id'];
        $amount = $item['amount'];

        // Depart Stock
        $departStock = DepartStock::where('id', $departStockId)->firstOrFail();


        $creatableDetails[] = [
          'depart_trans_process_id' => $newProcess->id,
          'sender_depart_stock_id' => $departStock->id,
          'amount_type_id' => $departStock->amount_type_id,
          'amount'  => $amount,
        ];
      }

      $newProcess->details()->createMany($creatableDetails);

      DB::commit();

      return [
        'message' => "Yuk muvaffaqiyatli yuborildi",
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      throw new ServerErrorException($e->getMessage(), (int) $e->getCode(), $e);
    }
  }

  public function findOne(int $id): array
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

  public function receive(array $data, int $id): array
  {
    // Req Data
    $reqDetailsList = $data['details_list'];

    // Send Status
    $sendStatus = DepartTransProcessService::getSendProductStatus();

    // Trans Process
    $transProcess = DepartTransProcess::with('details')
      ->where('status_id', $sendStatus->id)
      ->findOrFail($id);

    $senderDepartId = $transProcess->sender_depart_id;
    $receiverDepartId = $transProcess->receiver_depart_id;

    // Status
    $receivedStatus = DepartTransProcessService::getReceiveProductStatus();

    DB::beginTransaction();
    try {
      // Increment Stock
      foreach ($reqDetailsList as $reqItem) {
        // Values
        $reqItemId = $reqItem['item_id'];
        $departStockId = $reqItem['depart_stock_id'];

        // Transfer Item
        $transProcessItem = DepartTransProcessDetail::where('depart_trans_process_id', $transProcess->id)
          ->findOrFail($reqItemId);
        // Transfer Item Values
        $amountTypeId = $transProcessItem->amount_type_id;
        $senderDepartStockId = $transProcessItem->sender_depart_stock_id;



        // Sender Depart Stock
        $senderStockItem = DepartStock::where('depart_id', $senderDepartId)
          ->findOrFail($senderDepartStockId);

        // Change of Sender Department Stock

        if ($senderStockItem->amount < $transProcessItem->amount) {
          throw new InvalidDataException("Yuboruvchining `{$senderStockItem->polka->name}` polkasida zaxira yetarli emas!");
        }

        $senderStockItem->decrement('amount', $transProcessItem->amount);

        //Receiver Depart Stock
        $receiverStockItem = DepartStock::where('depart_id', $receiverDepartId)
          ->where('amount_type_id', $amountTypeId)
          ->findOrFail($departStockId);
        $receiverStockItem->increment('amount', $transProcessItem->amount);


        // Set Received Polka
        $transProcessItem->receive_depart_stock_id = $departStockId;
        $transProcessItem->save();
      }

      // Set Receiver User
      $transProcess->receiver_user_id = Auth::id();

      // Change Trans Process Status
      $transProcess->status_id = $receivedStatus->id;
      $transProcess->save();

      // Finish
      DB::commit();

      return [
        'message' => "Yuk muvaffaqiyatli qabul qilindi, zaxirani tekshiring",
        'code' => 201,
      ];
    } catch (\Exception $e) {
      DB::rollBack();

      throw new ServerErrorException($e->getMessage(), (int) $e->getCode(), $e);
    }
  }

  public static function getSendProductStatus(): Status
  {
    return StatusService::findByCode('departSendProduct');
  }
  public static function getReceiveProductStatus(): Status
  {
    return StatusService::findByCode('departReceiveProduct');
  }
  public static function getCancelProductStatus(): Status
  {
    return StatusService::findByCode('departCancelProduct');
  }
}
