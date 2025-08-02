<?php

namespace App\Services\Payment;

use App\Exceptions\InvalidDataException;
use App\Exceptions\ServerErrorException;
use App\Http\Resources\UserWalletResource;
use App\Models\Payment;
use App\Models\Supplier;
use App\Models\UserWallet;
use App\Services\Status\StatusService;
use App\Services\Utils\DateFormatter;
use App\Services\Wallet\UserWalletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentSupplierService
{
    private $authUser;

    public function __construct(
        protected UserWalletService $userWalletService
    ) {
        $this->authUser = Auth::user();
    }

    public function findAll(array $data): array
    {
        $startDate = DateFormatter::format($data['startDate'], 'start');
        $endDate = DateFormatter::format($data['endDate'], 'end');
        $supplierId = $data['supplier_id'] ?? null;

        $paymentTotalsQuery = DB::table('payment_wallet')
            ->select('payment_id', DB::raw('SUM(sum_price) as total_price'))
            ->groupBy('payment_id');

        $query = Payment::with(['paymentable', 'user', 'wallets', 'status'])
            ->joinSub($paymentTotalsQuery, 'payment_totals', function ($join) {
                $join->on('payments.id', '=', 'payment_totals.payment_id');
            })
            ->select('payments.*', 'payment_totals.total_price')
            ->where('paymentable_type', 'App\Models\Supplier')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->orderBy('payments.created_at', 'desc');

        if ($supplierId) {
            $query->where('payments.paymentable_id', $supplierId);
        }

        if (!$this->authUser->isAdmin()) {
            $query->where('user_id', $this->authUser->id);
        }

        // Data
        $data = $query->get();

        // Totals
        $totals = $this->getTotals($supplierId, [$startDate, $endDate]);

        return [
            'data' => $data,
            'totals' => $totals,
        ];
    }

    /**
     * @throws ServerErrorException
     * @throws InvalidDataException
     */
    public function create(array $data): array
    {
        // Data
        $supplierId = $data['supplier_id'];
        $reqWalletList = $data['wallet_list'];
        $comment = $data['comment'] ?? null;

        // Supplier
        $supplier = Supplier::findOrFail($supplierId);

        // Status Supplier Payment
        $statusSupplierPayment = StatusService::findByCode('paymentSupplier');

        DB::beginTransaction();

        try {
            // New Payment
            $newPayment = new Payment([
                'user_id' => $this->authUser->id,
                'status_id' => $statusSupplierPayment->id,
                'comment' => $comment,
            ]);

            $supplier->payments()->save($newPayment);

            // Sum Amount
            $sumAmount = 0;

            // Attach to Wallet
            $walletAttachList = [];
            foreach ($reqWalletList as $wallet) {
                $userWalletId = $wallet['user_wallet_id'];
                $amount = $wallet['amount'];
                $rateAmount = $wallet['rate_amount'];

                // User wallet
                $userWallet = UserWallet::findOrFail($userWalletId);

                $sumPrice = $amount * $rateAmount;

                $walletAttachList[$userWallet->wallet_id] = [
                    'amount' => $amount,
                    'rate_amount' => $rateAmount,
                    'sum_price' => $sumPrice,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];

                // Decrement User Wallet
                $this->userWalletService->decrement($userWallet->id, $amount);

                // Increment Sum Amount
                $sumAmount += $sumPrice;
            }

            $newPayment->wallets()->attach($walletAttachList);

            // Change Supplier Balance
            $supplier->decrement('balance', $sumAmount);

            DB::commit();

            $formatSum = number_format($sumAmount, 2, '.', ',');

            return [
                'message' => "$supplier->first_name $supplier->last_name taminotchiga $formatSum uzs  muvaffaqiyatli o'tkazildi",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function fineOne(int $supplierId): array
    {
        $paymentTotalsQuery = DB::table('payment_wallet')
            ->select('payment_id', DB::raw('SUM(sum_price) as total_price'))
            ->groupBy('payment_id');

        $query = Payment::with(['paymentable', 'user', 'wallets', 'status'])
            ->joinSub($paymentTotalsQuery, 'payment_totals', function ($join) {
                $join->on('payments.id', '=', 'payment_totals.payment_id');
            })
            ->select('payments.*', 'payment_totals.total_price')
            ->where('paymentable_type', 'App\Models\Supplier');


        if (!$this->authUser->isAdmin()) {
            $query->where('user_id', $this->authUser->id);
        }

        $data = $query->findOrFail($supplierId);

        return [
            'data' => $data,
        ];
    }

    private function getTotals(string|null $supplierId, array $date): array
    {
        $query = DB::table('payments')
            ->join('payment_wallet', 'payments.id', '=', 'payment_wallet.payment_id')
            ->select(
                DB::raw('SUM(payment_wallet.sum_price) as total_amount'),
                DB::raw('COUNT(payments.id) as total_count')
            )
            ->where('paymentable_type', 'App\Models\Supplier');

        if ($supplierId) {
            $query->where('payments.paymentable_id', $supplierId);
        }

        $query->whereBetween('payments.created_at', [$date[0], $date[1]])
            ->first();

        $data = $query->first();

        return [
            "total_amount" => (float)$data->total_amount,
            "total_count" => (int)$data->total_count,
        ];
    }
}
