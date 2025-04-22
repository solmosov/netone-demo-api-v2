<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartStockRequest;
use App\Services\Department\DepartStockService;
use Illuminate\Http\JsonResponse;

class DepartStockController extends Controller
{
    public function __construct(
        protected DepartStockService $departStockService
    )
    {
    }

    public function store(StoreDepartStockRequest $request): JsonResponse
    {
        $result = $this->departStockService->create($request->validated());

        return response()->json([
            'message' => $result['message']
        ], 201);
    }

    public function index(): JsonResponse
    {
        $data = $this->departStockService->findAll();

        return response()->json([
            'data' => $data
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $data = $this->departStockService->findOne((int) $id);

        return response()->json([
           'data' => $data
        ]);
    }
}
