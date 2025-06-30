<?php

namespace App\Services\Chart;

use App\Models\Payment;
use App\Services\Utils\DateFormatter;
use Illuminate\Support\Facades\DB;

class ChartExpenseService
{
    public function findAll(array $data): array
    {
        $startDate = DateFormatter::format($data['start_date']);
        $endDate = DateFormatter::format($data['end_date'], 'end');

        $groupBy = 'expense';
        $dateFormat = '%Y-%m-%d';

        $expense = Payment::join('payment_wallet', 'payments.id', '=', 'payment_wallet.payment_id')
            ->join('expenses', 'payments.paymentable_id', '=', 'expenses.id')
            ->select(
                DB::raw('SUM(payment_wallet.sum_price) as amount'),
                'expenses.name as expense'
            )
            ->where('payments.paymentable_type', 'App\Models\Expense')
            ->whereBetween('payments.payment_date', [$startDate, $endDate])
            ->groupBy('expense')
            ->get();

        // Totals
        foreach ($expense as $key => $value) {
            $expense[$key]->amount = (float) $value->amount;
        }

        $data = [
            'list' => $expense,
            'total_amount' => $expense->sum('amount'),
            'total_count' => $expense->count(),
        ];

        return $data;
    }
}
