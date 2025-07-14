<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetProductRequest;
use App\Http\Requests\GetProductStockRequest;
use App\Models\ProductStock;
use App\Http\Requests\StoreProductStockRequest;
use App\Http\Requests\UpdateProductStockRequest;
use App\Http\Resources\ProductStockResource;
use App\Services\Stock\ProductStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ProductStockController extends Controller
{
    public function __construct(
        protected ProductStockService $productStockService
    ) {}

    public function index(GetProductStockRequest $request): JsonResponse
    {
        // Gate
        Gate::authorize('viewAny', ProductStock::class);

        $data = $this->productStockService->findAll($request->validated());

        return response()->json([
            "data" => ProductStockResource::collection($data),
        ]);
    }


    public function store(StoreProductStockRequest $request): JsonResponse
    {
        // Gate
        Gate::authorize('create', ProductStock::class);

        $newStock = $this->productStockService->create($request->validated());

        return response()->json([
            'message' => "Mahsulot zahira polkasi muvaffaqiyatli qo'shildi",
            'data' => ProductStockResource::make($newStock)
        ], 201);
    }


    public function show(string $id): JsonResponse
    {
        // Gate
        Gate::authorize('view', ProductStock::class);

        $data = $this->productStockService->findOne((int)$id);

        return response()->json([
            "data" => ProductStockResource::make($data),
        ]);
    }


    public function update(UpdateProductStockRequest $request, string $id): JsonResponse
    {
        // Gate
        Gate::authorize('update', ProductStock::class);

        $productStock = $this->productStockService->update($request->validated(), (int)$id);

        return response()->json([
            'message' => "Mahsulot zahira polkasi muvaffaqiyatli tahrirlandi",
            'data' => ProductStockResource::make($productStock)
        ]);
    }


    public function destroy(string $id): JsonResponse
    {
        // Gate
        Gate::authorize('delete', ProductStock::class);

        $productStock = $this->productStockService->delete((int)$id);

        return response()->json([
            'message' => "Mahsulot zahira polkasi muvaffaqiyatli o'chirildi",
            'data' => ProductStockResource::make($productStock)
        ]);
    }
}
