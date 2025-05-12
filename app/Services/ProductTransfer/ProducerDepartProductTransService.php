<?php

namespace App\Services\ProductTransfer;

use App\Exceptions\ServerErrorException;
use App\Models\DepartStock;
use App\Models\ProducerProductTrans;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\assertContainsNotOnlyObject;

class ProducerDepartProductTransService
{
  public function findAll()
  {
    $data = ProducerProductTrans::where('is_sending_depart', false)->latest()->get();

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
}
