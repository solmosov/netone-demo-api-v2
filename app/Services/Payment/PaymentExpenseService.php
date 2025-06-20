<?php

namespace App\Services\Payment;

use App\Exceptions\InvalidDataException;
use App\Exceptions\ServerErrorException;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\UserWallet;
use App\Services\Status\StatusService;
use App\Services\Utils\DateFormatter;
use App\Services\Wallet\UserWalletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentExpenseService
{

    private $authUser;

    public function __construct(
        protected readonly UserWalletService $userWalletService,
    ) {
        $this->authUser = Auth::user();
    }

    public function findAll(array $data): array
    {
        $startDate = DateFormatter::format($data['startDate']);
        $endDate = DateFormatter::format($data['endDate'], 'end');
        $expenseId = $data['expense_id'] ?? null;

        $query = Payment::with('paymentable', 'user', 'wallets', 'status')
            ->where('paymentable_type', 'App\Models\Expense')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc');

        if (!$this->authUser->isAdmin()) {
            $query->where('user_id', $this->authUser->id);
        }

        // All data
        $data = $query->get();

        // Totals
        $totals = $this->getTotals($expenseId, [$startDate, $endDate]);

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
        // Auth User
        $authUser = Auth::user();

        $expenseId = $data['expense_id'];
        $userWalletId = $data['user_wallet_id'];
        $amount = $data['amount'];
        $rateAmount = $data['rate_amount'];
        $comment = $data['comment'] ?? null;
        $paymentDate = $data["payment_date"] ?? date('Y-m-d');

        // Validation User Wallet
        $userWallet = UserWallet::with('wallet.currency')
            ->where('user_id', $authUser->id)
            ->findOrFail($userWalletId);

        // Expense
        $expense = Expense::findOrFail($expenseId);

        // Status Payment Expense
        $statusPaymentExpense = StatusService::findByCode('paymentExpense');

        DB::beginTransaction();
        try {
            // New Payment For Expense
            $payment = new Payment([
                'user_id' => $authUser->id,
                'status_id' => $statusPaymentExpense->id,
                'comment' => $comment,
                'payment_date' => $paymentDate
            ]);
            $expense->payments()->save($payment);

            // Attach Amount To Wallet
            $payment->wallets()->attach($userWallet->wallet_id, [
                'amount' => $amount,
                'rate_amount' => $rateAmount,
                'sum_price' => $amount * $rateAmount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Decrement from User Wallet
            $this->userWalletService->decrement($userWallet->id, $amount);

            // Finish
            DB::commit();

            // Amount Currency
            $currency = $userWallet->wallet->currency->symbol;

            $formatNumVal = number_format($amount, 2, '.', ',');

            return [
                'message' => "`$expense->name` xarajat uchun $formatNumVal $currency  muvaffaqiyatli o'tkazildi!",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ServerErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function findOne(int $expenseId): array
    {
        $query = Payment::with('paymentable', 'user', 'wallets', 'status')
            ->where('paymentable_type', 'App\Models\Expense')
            ->orderBy('created_at', 'desc');

        if (!$this->authUser->isAdmin()) {
            $query->where('user_id', $this->authUser->id);
        }

        $data = $query->findOrFail($expenseId);

        return [
            'data' => $data,
        ];
    }

    private function getTotals(string|null $expenseId, array $date): array
    {
        $query = DB::table('payments')
            ->join('payment_wallet', 'payments.id', '=', 'payment_wallet.payment_id')
            ->select(
                DB::raw('SUM(payment_wallet.sum_price) as total_amount'),
                DB::raw('COUNT(payments.id) as total_count')
            )
            ->where('paymentable_type', 'App\Models\Expense');

        if ($expenseId) {
            $query->where('payments.paymentable_id', $expenseId);
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
