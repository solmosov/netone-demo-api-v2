<?php

namespace App\Http\Controllers;

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
    )
    {
    }

    public function index(): JsonResponse
    {
        // Gate
        Gate::authorize('viewAny', ProductStock::class);

        $data = $this->productStockService->findAll();

        return response()->json([
            "data" => ProductStockResource::collection($data),
        ]);
    }


    public function store(StoreProductStockRequest $request): JsonResponse
    {
        // Gate
        Gate::authorize('create', ProductStock::class);

        $newStock = ProductStock::create($request->validated());

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


    public function update(UpdateProductStockRequest $request, ProductStock $productStock)
    {
        // Gate
        Gate::authorize('update', ProductStock::class);

        $productStock->update($request->validated());

        return response()->json([
            'message' => "Mahsulot zahira polkasi muvaffaqiyatli tahrirlandi",
            'data' => ProductStockResource::make($productStock)
        ]);
    }


    public function destroy(ProductStock $productStock): JsonResponse
    {
        // Gate
        Gate::authorize('delete', ProductStock::class);

        $productStock->delete();

        return response()->json([
            'message' => "Mahsulot zahira polkasi muvaffaqiyatli o'chirildi",
            'data' => ProductStockResource::make($productStock)
        ]);
    }
}
