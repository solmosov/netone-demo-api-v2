<?php


namespace App\Services\Chart;

use App\Models\Order;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class SalesExpenseProfitService
{
  public function findAll(array $queryParams)
  {
    $year = $queryParams['year'] ?? now()->format('Y');

    // 12 ta oy uchun label va keylar
    $dates = collect(range(1, 12))->map(function ($month) use ($year) {
        $date = \Carbon\Carbon::createFromDate($year, $month, 1);
        return [
            'label' => $date->format('M'), // Masalan: Jan, Feb, ...
            'key' => $date->format('Y-m'), // Masalan: 2023-01, 2023-02, ...
        ];
    });

    $dateFormat = '%Y-%m';
    $groupBy = 'month';
    $startDate = \Carbon\Carbon::createFromDate($year, 1, 1)->startOfYear();
    $endDate = \Carbon\Carbon::createFromDate($year, 12, 31)->endOfYear();

    // Sotuv (Sales)
    $sales = Order::select(DB::raw("DATE_FORMAT(created_at, '$dateFormat') as $groupBy"), DB::raw('SUM(total_sale_price) as total'))
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy($groupBy)
        ->pluck('total', $groupBy);

    // Xarajat (Expenses)
    $expenses = Expense::select(DB::raw("DATE_FORMAT(created_at, '$dateFormat') as $groupBy"), DB::raw('SUM(amount) as total'))
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy($groupBy)
        ->pluck('total', $groupBy);

    // Massivni frontend uchun tayyorlash
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
