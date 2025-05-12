<?php

namespace App\Services\ProductTransfer;

use App\Exceptions\InvalidDataException;
use App\Models\ProducerProductTrans;
use App\Models\ProductStock;
use App\Services\Status\StatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProducerProductTransService
{
  public function findAll() {}
  public function findOne() {}
  public function sendProductToDepart(array $data)
  {
    // Data
    $reqDepartId = $data['depart_id'];
    $reqItemsList = $data['items_list'];
    $reqComment = $data['comment'] ?? null;

    // Auth User Producer
    $authUser = Auth::user();

    // Status
    $statusSend = self::getStatusSend();

    try {
      // New Transfer
      $newTransfer = ProducerProductTrans::create([
        'producer_id' => $authUser->id,
        'depart_id' => $reqDepartId,
        'status_id' => $statusSend->id,
        'is_sending_depart' => true,
        'comment' => $reqComment,
      ]);

      // New Transfer Details
      $details = [];
      foreach ($reqItemsList as $item) {
        $productStockId = $item['product_stock_id'];
        $amount = $item['amount'];

        // Product Stock
        $productStock = ProductStock::findOrFail($productStockId);

        $details[] = [
          'trans_id' => $newTransfer->id,
          'product_stock_id' => $productStock->id,
          'amount_type_id' => $productStock->amount_type_id,
          'amount' => $amount
        ];
      }
      $newTransfer->details()->createMany($details);

      DB::commit();

      return $newTransfer;
    } catch (\Exception $e) {
      DB::rollBack();
      throw new InvalidDataException($e->getMessage(), (int) $e->getCode(), $e);
    }
  }

  public static function getStatusSend()
  {
    return StatusService::findByCode('producerSendProduct');
  }
  public static function getStatusReceive()
  {
    return StatusService::findByCode('producerReceiveProduct');
  }
  public static function getStatusCancel()
  {
    return StatusService::findByCode('producerCancelProduct');
  }
}
