<?php

namespace App\Services\ProductTransfer;

use App\Exceptions\ServerErrorException;
use App\Models\DepartStock;
use App\Models\ProducerProductTrans;
use App\Models\ProducerProductTransDetails;
use App\Models\ProductStock;
use App\Services\Department\DepartTransProcessService;
use App\Services\Status\StatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProducerDepartProductTransService
{
  public function findAll()
  {
    $data = ProducerProductTrans::with([
      'producer',
      'depart',
      'departUser',
      'status',
    ])
      ->where('is_sending_depart', false)
      ->latest()
      ->get();

    return $data;
  }
  public function findOne() {}
  public function create(array $data)
  {
    // Auth User
    $authUser = Auth::user();

    // Req Data
    $reqDepartId = $data['depart_id'];
    $reqProducerId = $data['producer_id'];
    $reqItemsList = $data['items_list'];
    $reqComment = $data['comment'];

    // Status Send
    $statusSend = ProducerProductTransService::getStatusSend();

    try {
      // New Transfer
      $newTransfer = ProducerProductTrans::create([
        'producer_id' => $reqProducerId,
        'depart_id' => $reqDepartId,
        'depart_user_id' => $authUser->id, // nullable
        'status_id' => $statusSend->id,
        'is_sending_depart' => false,
        'comment' => $reqComment,
      ]);

      // New Transfer Details
      $details = [];
      foreach ($reqItemsList as $item) {
        $departStockId = $item['depart_stock_id'];
        $amount = $item['amount'];

        // Department Stock
        $departStock = DepartStock::findOrFail($departStockId);

        $details[] = [
          'trans_id' => $newTransfer->id,
          'depart_stock_id' => $departStock->id, // not required
          'amount_type_id' => $departStock->amount_type_id,
          'amount' => $amount
        ];
      }
      $newTransfer->details()->createMany($details);

      DB::commit();

      return $newTransfer;
    } catch (\Exception $e) {
      DB::rollBack();
      throw new ServerErrorException($e->getMessage(), (int) $e->getCode(), $e);
    }
  }

  public function receive(int $id, array $data)
  {
    // Req Data
    $itemsList = $data['items_list'];

    // Status Send Product
    $statusSendProduct = StatusService::findByCode('producerSendProduct');

    // Transfer
    $transfer = ProducerProductTrans::where('status_id', $statusSendProduct->id)->findOrFail($id);

    // Status Received
    $statusReceived = DepartTransProcessService::getReceiveProductStatus();

    DB::beginTransaction();
    try {
      // Transfer Items
      foreach ($itemsList as $item) {
        $itemId = $item['item_id'];
        $departStockId = $item['depart_stock_id'];

        // Transfer Item
        $transferItem = ProducerProductTransDetails::findOrFail($itemId);
        $amountTypeId = $transferItem->amount_type_id;
        $amount = $transferItem->amount;

        // Product Stock
        $productStock = ProductStock::findOrFail($transferItem->product_stock_id);

        // Depart Stock
        $departStock = DepartStock::where('amount_type_id', $amountTypeId)->findOrFail($departStockId);

        $departStock->increment('amount', $amount);
        $productStock->decrement('amount', $amount);

        $transferItem->depart_stock_id = $departStock->id;
        $transferItem->save();
      }

      // Change Transfer status
      $transfer->status_id = $statusReceived->id;
      $transfer->depart_user_id = Auth::id();
      $transfer->save();

      // Finish
      DB::commit();

      return $transfer;
    } catch (\Exception $e) {
      DB::rollBack();
      throw new ServerErrorException($e->getMessage(), (int)$e->getCode(), $e);
    }
  }
}
