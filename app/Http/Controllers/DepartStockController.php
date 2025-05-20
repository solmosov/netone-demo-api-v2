<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartStockRequest;
use App\Http\Resources\DepartStockResource;
use App\Models\DepartStock;
use App\Services\Department\DepartStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DepartStockController extends Controller
{
    public function __construct(
        protected DepartStockService $departStockService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // Gate
        Gate::authorize('viewAny', DepartStock::class);

        $validated = $request->validate([
            'depart_id' => 'nullable|integer|exists:departs,id',
        ]);

        $data = $this->departStockService->findAll($validated);

        return response()->json([
            'data' => DepartStockResource::collection($data),
        ]);
    }

    public function store(StoreDepartStockRequest $request): JsonResponse
    {
        // Gate
        Gate::authorize('create', DepartStock::class);

        $result = $this->departStockService->create($request->validated());

        return response()->json([
            'message' => "Yangi bo'lim zaxirasi qo'shildi",
            'data' => DepartStockResource::make($result)
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        // Gate
        Gate::authorize('view', DepartStock::class);

        $data = $this->departStockService->findOne((int) $id);

        return response()->json([
            'data' => DepartStockResource::make($data),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        // Gate
        Gate::authorize('delete', DepartStock::class);

        $data = $this->departStockService->delete((int) $id);

        return response()->json([
            'message' => "Bo'lim zaxira polkasi o'chirildi",
            'data' => DepartStockResource::make($data),
        ]);
    }
}
