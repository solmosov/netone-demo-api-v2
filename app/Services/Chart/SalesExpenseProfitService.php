<?php


namespace App\Services\Chart;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use App\Services\Utils\DateFormatter;

class SalesExpenseProfitService
{
  public function findAll(array $queryParams)
  {
    $year = $queryParams['year'] ?? now()->format('Y');

    // Uzbek month names
    $uzMonths = DateFormatter::uzbekMonths();

    $dates = collect(range(1, 12))->map(function ($month) use ($year, $uzMonths) {
        $date = \Carbon\Carbon::createFromDate($year, $month, 1);
        return [
            'label' => $uzMonths[$month],
            'key' => $date->format('Y-m'),
        ];
    });

    $dateFormat = '%Y-%m';
    $groupBy = 'month';
    $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear();
    $endDate = \Carbon\Carbon::createFromDate($year, 12, 31)->endOfYear();

    // Sales
    $sales = Order::select(DB::raw("DATE_FORMAT(created_at, '$dateFormat') as $groupBy"), DB::raw('SUM(total_sale_price) as total'))
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy($groupBy)
        ->pluck('total', $groupBy);

    // Expenses from Payments table
    $expenses = Payment::join('payment_wallet', 'payments.id', '=', 'payment_wallet.payment_id')
        ->select(DB::raw("DATE_FORMAT(payments.created_at, '$dateFormat') as $groupBy"), DB::raw('SUM(payment_wallet.sum_price) as total'))
        ->where('payments.paymentable_type', 'App\Models\Expense')
        ->whereBetween('payments.created_at', [$startDate, $endDate])
        ->groupBy($groupBy)
        ->pluck('total', $groupBy);

    // Prepare array for frontend
    $result = [];
    foreach ($dates as $date) {
        $sotuv = (float)($sales[$date['key']] ?? 0);
        $xarajat = (float)($expenses[$date['key']] ?? 0);
        $qoldi = $sotuv - $xarajat;
        $result[] = [
            'name' => $date['label'],
            'sotuv' => $sotuv,
            'xarajat' => $xarajat,
            'qoldi' => $qoldi,
        ];
    }

    return $result;
  }
}
