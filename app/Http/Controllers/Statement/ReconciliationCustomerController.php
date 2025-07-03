<?php

namespace App\Http\Controllers\Statement;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetDateInternalRequest;
use App\Http\Resources\OrderReturnResource;
use App\Http\Resources\OrderReturnShowResource;
use App\Http\Resources\PaymentCustomerResource;
use App\Http\Resources\ReconciliationCustomerCreditResource;
use App\Http\Resources\ReconciliationCustomerDetailsDebitResource;
use App\Models\Customer;
use App\Services\Statement\ReconciliationCustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReconciliationCustomerController extends Controller
{
    public function __construct(
        protected ReconciliationCustomerService $reconciliationService
    )
    {
    }

    public function show(GetDateInternalRequest $request, int $customerId): JsonResponse
    {
        if (!Customer::findOrFail($customerId)) abort(404);

        $data = $this->reconciliationService->getByCustomer($request->validated(), $customerId);

        return response()->json($data);
    }

    public function details(GetDateInternalRequest $request, int $customerId)
    {
    }

    public function detailsDebit(GetDateInternalRequest $request, int $customerId): JsonResponse
    {
        $result = $this->reconciliationService->detailsDebit($request->validated(), $customerId);

        return response()->json([
            'data' => ReconciliationCustomerDetailsDebitResource::collection($result),
        ]);
    }

    public function detailsReturn(GetDateInternalRequest $request, int $customerId): JsonResponse
    {
        $result = $this->reconciliationService->detailsReturn($request->validated(), $customerId);

        return response()->json([
            'data' => OrderReturnShowResource::collection($result),
        ]);
    }

    public function detailsCredit(GetDateInternalRequest $request, int $customerId)
    {
        $result = $this->reconciliationService->detailsCredit($request->validated(), $customerId);

        return response()->json([
            'data' => ReconciliationCustomerCreditResource::collection($result),
        ]);
    }
}
