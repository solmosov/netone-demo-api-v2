<?php

namespace App\Http\Requests;

use App\Exceptions\InvalidDataException;
use App\Exceptions\NotFoundException;
use App\Models\DepartStock;
use App\Models\ProductionProcess;
use App\Services\Status\StatusService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class FinishProductionProcessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'depart_stock_id' => 'required|integer|exists:depart_stocks,id',
            'out_amount' => 'required|numeric|min:0'
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $authUser = Auth::user();

            // Request
            $processId = $this->route('id');
            $departStockId = $this->input('depart_stock_id');

            // Production Process
            $process = ProductionProcess::with(['productionRecipe'])->findOrFail($processId);
            $outProductId = $process->productionRecipe->out_product_id;
            $outAmountTypeId = $process->productionRecipe->out_amount_type_id;

            // Status of Current Process
            $statusCurrent = StatusService::findById($process->status_id);

            // Status Validation
            if ($statusCurrent->code !== 'productionProcess') {
                if ($statusCurrent->code === 'productionCancel') {
                    throw new InvalidDataException('Bu ishlab chiqarish jarayoni allaqachon bekor qilingan');
                } else if ($statusCurrent->code === 'productionStopped') {
                    throw new InvalidDataException("Bu ishlab chiqarish jarayoni allaqachon to'xtatilgan");
                } else if ($statusCurrent->code === 'productionCompleted') {
                    throw new InvalidDataException("Bu ishlab chiqarish jarayoni allaqachon tayyorlangan");
                }

                throw new InvalidDataException("Bu ishlab chiqarish jarayonini tugallab bo'lmaydi");
            }

            // Depart Stock
            $departStock = DepartStock::findOrFail($departStockId);

            // Depart User
            $exists = DB::table('depart_user')
                ->where('user_id', $authUser->id)
                ->where('depart_id', $departStock->depart_id);
            if (!$exists) {
                throw new NotFoundException("Zaxira polka mavjud emas");
            } else if ($departStock->product_id != $outProductId) {
                throw new InvalidDataException("Zaxira polka, ishlab chiqariladigan mahsulotga tegishli emas");
            } else if ($departStock->amount_type_id !== $outAmountTypeId) {
                throw new InvalidDataException("Zaxira polkaning o'lchov birligi ishlab chiqariladigan mahsulotga mos emas");
            }
        });
    }
}
