<?php

namespace App\Services\Payment;

use App\Exceptions\ServerErrorException;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\UserWallet;
use App\Services\Status\StatusService;
use App\Services\Utils\DateFormatter;
use App\Services\Wallet\UserWalletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentCustomerService
{
    private $authUser;

    public function __construct(
        protected readonly UserWalletService $userWalletService,

    ) {
        $this->authUser = Auth::user();
    }

    public function findAll(array $data): array
    {
        $startDate = DateFormatter::format($data['startDate'], 'start');
        $endDate = DateFormatter::format($data['endDate'], 'end');
        $customerId = $data['customer_id'] ?? null;

        $paymentTotalsQuery = DB::table('payment_wallet')
            ->select('payment_id', DB::raw('SUM(sum_price) as total_price'))
            ->groupBy('payment_id');

        $query = Payment::with(['paymentable', 'user', 'wallets', 'status'])
            ->joinSub($paymentTotalsQuery, 'payment_totals', function ($join) {
                $join->on('payments.id', '=', 'payment_totals.payment_id');
            })
            ->select('payments.*', 'payment_totals.total_price')
            ->where('paymentable_type', 'App\Models\Customer')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->orderBy('payments.created_at', 'desc');

        if ($customerId) {
            $query->where('payments.paymentable_id', $customerId);
        }


        if (!$this->authUser->isAdmin()) {
            $query->where('user_id', $this->authUser->id);
        }

        //  Data
        $data = $query->get();

        // Totals
        $totals = $this->getTotals($customerId, [$startDate, $endDate]);

        return [
            'data' => $data,
            'totals' => $totals,
        ];
    }

    /**
     * @throws ServerErrorException
     */
    public function create(array $data): array
    {
        $authUser = Auth::user();

        // Data
        $customerId = $data['customer_id'];
        $reqWalletList = $data['wallet_list'];
        $comment = $data['comment'] ?? null;

        // Customer
        $customer = Customer::findOrFail($customerId);

        // Status Payment Debt Customer
        $statusDebtCustomer = StatusService::findByCode('paymentCustomer');

        DB::beginTransaction();

        try {
            // New Payment For Customer
            $payment = new Payment([
                "user_id" => $authUser->id,
                'status_id' => $statusDebtCustomer->id,
                "comment" => $comment,
            ]);

            $customer->payments()->save($payment);

            // Convert To uzs
            $sumAmount = 0;

            // Attach Wallets
            $walletAttachList = [];
            foreach ($reqWalletList as $item) {
                $userWalletId = $item['user_wallet_id'];
                $amount = $item['amount'];
                $rateAmount = $item['rate_amount'];

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

                // Increment User Wallet
                $this->userWalletService->increment($userWalletId, $amount);

                // Increment Sum Amount
                $sumAmount += $sumPrice;
            }

            // Set to payment section
            $payment->wallets()->attach($walletAttachList);

            // Change Customer Balance
            $customer->increment('balance', $sumAmount);

            DB::commit();

            $formatSum = number_format($sumAmount, 2, '.', ',');

            return [
                'message' => "$customer->first_name $customer->last_name mijozdan $formatSum uzs o'tkazma muvaffaqiyatli qabul qilindi! Mijozning balansini tekshiring"
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function findOne(int $id): array
    {
        $paymentTotalsQuery = DB::table('payment_wallet')
            ->select('payment_id', DB::raw('SUM(sum_price) as total_price'))
            ->groupBy('payment_id');

        $query = Payment::with(['paymentable', 'user', 'wallets', 'status'])
            ->joinSub($paymentTotalsQuery, 'payment_totals', function ($join) {
                $join->on('payments.id', '=', 'payment_totals.payment_id');
            })
            ->select('payments.*', 'payment_totals.total_price')
            ->where('paymentable_type', 'App\Models\Customer');

        if (!$this->authUser->isAdmin()) {
            $query->where('user_id', $this->authUser->id);
        }

        $data = $query->findOrFail($id);

        return [
            'data' => $data,
        ];
    }

    private function getTotals(string|null $customerId, array $date): array
    {
        $query = DB::table('payments')
            ->join('payment_wallet', 'payments.id', '=', 'payment_wallet.payment_id')
            ->select(
                DB::raw('SUM(payment_wallet.sum_price) as total_amount'),
                DB::raw('COUNT(payments.id) as total_count')
            )
            ->where('paymentable_type', 'App\Models\Customer');

        if ($customerId) {
            $query->where('payments.paymentable_id', $customerId);
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
