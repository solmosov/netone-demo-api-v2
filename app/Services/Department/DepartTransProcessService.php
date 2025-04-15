<?php

namespace App\Services\Department;

use App\Exceptions\InvalidDataException;
use App\Exceptions\ServerErrorException;
use App\Models\Depart;
use App\Models\Department\DepartTransProcessDetail;
use App\Models\DepartStock;
use App\Models\DepartTransProcess;
use App\Models\Polka;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Status;
use App\Services\Status\StatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

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

    // Validate
    $exists = DB::table('depart_user')->where('user_id', Auth::id())->where('depart_id', $senderDepartId)->exists();
    if(!$exists){
      throw new InvalidDataException("Mahsulotlarini yuborish mumkin emas",403);
    }

    // Check Polka Owner Validation
    $pluckPolka = array_column($productList, 'polka_id');
    $exists = Polka::whereIn('id', $pluckPolka)->where('owner_id', '!=', Auth::id())->exists();
    if($exists){
      throw new InvalidDataException("O'zingizga tegishli bo'lgan polkani tanlang",403);
    }

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

    $count  = array_column($reqDetailsList, 'item_id');
    if ($transProcess->details()->count() !== sizeof($count)) {
      throw new InvalidDataException("O'tkazma mahsulotlari to'liq emas");
    }

    // Receiver Depart Id
    $senderDepartId = $transProcess->sender_depart_id;
    $receiverDepartId = $transProcess->receiver_depart_id;

    $exists = DB::table('depart_user')
      ->where('depart_id', $receiverDepartId)
      ->where('user_id', Auth::id());
    if (!$exists) {
      throw new InvalidDataException("Bu amalni bajarib bo'lmadi", 403);
    }
    
    // Check Polka Owner Validation
    $pluckPolka = array_column($reqDetailsList, 'polka_id');
    $exists = Polka::whereIn('id', $pluckPolka)->where('owner_id', '!=', Auth::id())->exists();
    if($exists){
      throw new InvalidDataException("O'zingizga tegishli bo'lgan polkani tanlang",403);
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
        $transProcessItem = DepartTransProcessDetail::where('depart_trans_process_id', $transProcess->id)
          ->findOrFail($reqItemId);
        $senderPolkaId = $transProcessItem->polka_id;

        //Receiver Depart Stock
        $receiverStockItem = DepartStock::where('depart_id', $receiverDepartId)
          ->where('amount_type_id', $transProcessItem->amount_type_id)
          ->where('polka_id', $reqPolkaId)
          ->firstOrFail();

        // Sender Depart Stock
        $senderStockItem = DepartStock::with('polka')
          ->where('depart_id', $senderDepartId)
          ->where('polka_id', $senderPolkaId)
          ->firstOrFail();

        // Change Stock
        $senderStockItem->decrement('amount', $transProcessItem->amount);
        if ($senderStockItem->amount < $transProcessItem->amount) {
          throw new InvalidDataException("Yuboruvchining {$senderStockItem->polka->name} polkasida zaxira yetarli emas!");
        }

        $receiverStockItem->increment('amount', $transProcessItem->amount);


        // Set Received Polka
        $transProcessItem->received_polka_id = $reqPolkaId;
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

      return [
        'message' => $e->getMessage(),
        'code' => $e->getCode() > 0 ? $e->getCode() : 404,
      ];
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
