<?php

namespace App\Http\Requests;

use App\Exceptions\InvalidDataException;
use App\Exceptions\NotFoundException;
use App\Models\Department\DepartTransProcessDetail;
use App\Models\DepartStock;
use App\Models\DepartTransPermProperty;
use App\Models\DepartTransPermPropertyDetails;
use App\Models\DepartTransProcess;
use App\Models\ProcessItem;
use App\Models\TransPerm;
use App\Services\Department\DepartTransPermService;
use App\Services\Department\DepartTransProcessService;
use ErrorException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class StoreDepartTransProcessReceiveRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::user()->isDepartUser();
    }


    public function rules(): array
    {
        return [
            'details_list' => 'required|array',
            'details_list.*.item_id' => 'required|integer|exists:depart_trans_process_details,id',
            'details_list.*.depart_stock_id' => 'required|integer|exists:depart_stocks,id'
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $authId = Auth::id();
            $transId = $this->route('trans_process_id');
            $detailsList = $this->input('details_list');

            // Status
            $accessStatus = DepartTransProcessService::getSendProductStatus();

            // Trans Process
            $transProcess = DepartTransProcess::with('details')
                ->where('status_id', $accessStatus->id)
                ->findOrFail($transId);
            $senderDepartId = $transProcess->sender_depart_id;
            $receiverDepartId = $transProcess->receiver_depart_id;
            $detailsCount = $transProcess->details->count();

            $exists = DB::table('depart_user')
                ->where('depart_id', $receiverDepartId)
                ->where('user_id', $authId)
                ->exists();

            if (!$exists) {
                throw new InvalidDataException("Bu o'tkazma siz uchun emas!", 403);
            } else {
                // Receive Trans Perm
                $receiveTransPerm = DepartTransPermService::getReceiveProduct();

                // Check Access
                $departTransPerm = DepartTransPermProperty::where('trans_perm_id', $receiveTransPerm->id)
                    ->where('depart_id', $receiverDepartId)
                    ->first();

                if (!$departTransPerm) {
                    throw new NotFoundException("Sizda qabul qilish ruxsati mavjud emas!");
                }

                $exists = DepartTransPermPropertyDetails::where('property_id', $departTransPerm->id)
                    ->where('depart_id', $senderDepartId)
                    ->exists();
                if (!$exists) {
                    $validator->errors()->add('receiver_depart_id', "Bu bo'limdan mahsulotlarni qabul qilish uchun sizda ruxsat mavjud emas!");
                } else if (count($detailsList) < $detailsCount) {
                    $validator->errors()->add("details_list", "Mahsulotlarni to'liq qabul qilishingiz kerak!");
                } else {
                    foreach ($detailsList as $key => $item) {
                        $itemId = $item['item_id'];
                        $departStockId = $item['depart_stock_id'];

                        // Process Item
                        $processItem = DepartTransProcessDetail::findOrFail($itemId);

                        // Department Stock
                        $departStock = DepartStock::where('id', $departStockId)
                            ->where('depart_id', $receiverDepartId)
                            ->first();
                        if (!$departStock) {
                            $validator->errors()->add("details_list_{$key}_depart_stock_id", "Bo'lim zaxira polkasini to'g'ri tanlang");
                        } else if ($departStock->amount_type_id !== $processItem->amount_type_id) {
                            $validator->errors()->add("details_list_{$key}_depart_stock_id", "Yuborilgan mahsulot bilan qabul qilinadigan zaxira polkangiz o'lchov birligi mos emas");
                        }
                    }
                }
            }
        });
    }
}
