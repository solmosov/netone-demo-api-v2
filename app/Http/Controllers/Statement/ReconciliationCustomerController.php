<?php

namespace App\Http\Controllers\Statement;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetDateInternalRequest;
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
    public function detailsDebit(GetDateInternalRequest $request, int $customerId)
    {
        $this->reconciliationService->detailsDebit($request->validated(), $customerId);
    }

    public function detailsReturn(GetDateInternalRequest $request, int $customerId)
    {
    }

    public function detailsCredit(GetDateInternalRequest $request, int $customerId)
    {
    }
}
