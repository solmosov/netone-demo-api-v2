<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartStockRequest;
use App\Http\Resources\DepartStockResource;
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
            'message' => "Yangi bo'lim zaxira qo'shildi",
            'data' => DepartStockResource::make($result)
        ], 201);
    }

    public function index(): JsonResponse
    {
        $data = $this->departStockService->findAll();

        return response()->json([
            'data' => DepartStockResource::collection($data),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $data = $this->departStockService->findOne((int) $id);

        return response()->json([
           'data' => DepartStockResource::make($data),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $data = $this->departStockService->delete((int) $id);

        return response()->json([
            'message' => "Bo'lim zaxira polkasi o'chirildi",
            'data' => DepartStockResource::make($data),
        ]);
    }
}
