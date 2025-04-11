<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartStockRequest;
use App\Services\Department\DepartStockService;

class DepartStockController extends Controller
{
    public function __construct(
        protected DepartStockService $departStockService
    ) {}

    public function store(StoreDepartStockRequest $request)
    {
        $result = $this->departStockService->create($request->validated());

        return response()->json([
            'message' => $result['message']
        ], 201);
    }
}
