<?php


namespace App\Services\Chart;

use App\Models\Order;
use App\Models\Expense;
use App\Models\ProductStock;
use App\Services\Utils\DateFormatter;
use Illuminate\Support\Facades\DB;

class SalesExpenseProfitService
{
  public function findAll(array $queryParams)
  {
    $dateType = $queryParams['date_type'] ?? 'monthly'; // 'yearly', 'monthly', 'weekly'

    if ($dateType === 'yearly') {
        $labels = collect(range(0, 11))->map(function ($i) {
            return now()->subMonths($i)->format('M Y');
        })->reverse()->values();
        $dateFormat = '%Y-%m';
        $groupBy = 'month';
        $startDate = now()->subMonths(11)->startOfMonth();
    } elseif ($dateType === 'weekly') {
        $labels = collect(range(0, 6))->map(function ($i) {
            return now()->subDays($i)->format('D d');
        })->reverse()->values();
        $dateFormat = '%Y-%m-%d';
        $groupBy = 'day';
        $startDate = now()->subDays(6)->startOfDay();
    } else { // monthly (default)
        $labels = collect(range(0, 29))->map(function ($i) {
            return now()->subDays($i)->format('d M');
        })->reverse()->values();
        $dateFormat = '%Y-%m-%d';
        $groupBy = 'day';
        $startDate = now()->subDays(29)->startOfDay();
    }

    // Sotuv (Sales)
    $sales = Order::select(DB::raw("DATE_FORMAT(created_at, '$dateFormat') as $groupBy"), DB::raw('SUM(total_sale_price) as total'))
        ->where('created_at', '>=', $startDate)
        ->groupBy($groupBy)
        ->pluck('total', $groupBy);

    // Xarajat (Expenses)
    $expenses = Expense::select(DB::raw("DATE_FORMAT(created_at, '$dateFormat') as $groupBy"), DB::raw('SUM(amount) as total'))
        ->where('created_at', '>=', $startDate)
        ->groupBy($groupBy)
        ->pluck('total', $groupBy);

    // Massivni frontend uchun tayyorlash
    $result = [];
    foreach ($labels as $label) {
        // $key bazaga mos formatda bo'lishi kerak
        if ($dateType === 'yearly') {
            $key = \Carbon\Carbon::createFromFormat('M Y', $label)->format('Y-m');
        } else {
            $key = \Carbon\Carbon::parse($label)->format($dateFormat);
        }
        $sotuv = (float)($sales[$key] ?? 0);
        $xarajat = (float)($expenses[$key] ?? 0);
        $qoldi = $sotuv - $xarajat;
        $result[] = [
            'name' => $label,
            'sotuv' => $sotuv,
            'xarajat' => $xarajat,
            'qoldi' => $qoldi,
        ];
    }

    return $result;
  }
}
