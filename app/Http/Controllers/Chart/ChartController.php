<?php

namespace App\Http\Controllers\Chart;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetChartByYearlyRequest;
use App\Http\Requests\GetChartExpenseRequest;
use App\Services\Chart\ChartExpenseService;
use App\Services\Chart\SalesExpenseProfitService;
use Illuminate\Http\JsonResponse;

class ChartController extends Controller
{
    public function __construct(
        protected readonly SalesExpenseProfitService $salesExpenseProfitService,
        protected readonly ChartExpenseService $expenseService
    )
    {
    }

    public function salesExpenseProfit(GetChartByYearlyRequest $request): JsonResponse
    {
        $data = $this->salesExpenseProfitService->findAll($request->validated());
        return response()->json([
            'data' => $data
        ]);
    }

    public function expense(GetChartExpenseRequest $request): JsonResponse
    {
        $result = $this->expenseService->findAll($request->validated());
        return response()->json($result);
    }
}
