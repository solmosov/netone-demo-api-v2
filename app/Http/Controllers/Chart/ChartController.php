<?php

namespace App\Http\Controllers\Chart;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetChartRequest;
use App\Http\Requests\QueryParameterRequest;
use Illuminate\Http\Request;
use App\Services\Chart\SalesExpenseProfitService;
use Illuminate\Http\JsonResponse;

class ChartController extends Controller
{
    public function __construct(
        protected readonly SalesExpenseProfitService $service
    ) {}

    public function salesExpenseProfit(GetChartRequest $request): JsonResponse
    {
        $data = $this->service->findAll($request->validated());
        return response()->json([
            'data' => $data
        ]);
    }
}
