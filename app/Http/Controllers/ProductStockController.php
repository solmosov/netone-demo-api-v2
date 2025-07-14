<?php

namespace App\Http\Controllers;

use App\Models\ProductStock;
use App\Http\Requests\StoreProductStockRequest;
use App\Http\Requests\UpdateProductStockRequest;
use App\Http\Resources\ProductStockResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ProductStockController extends Controller
{
    public function index()
    {
        // Gate
        Gate::authorize('viewAny', ProductStock::class);

        $data = ProductStock::with('polka', 'product', 'amountType')
            ->selectRaw('*,
                CASE
                    WHEN max_amount=0 AND min_amount=0 THEN NULL
                    WHEN max_amount = min_amount THEN 0
                    ELSE ((amount - min_amount) / (max_amount - min_amount) * 100)
                END as percentage')
            ->get();

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

        $data = ProductStock::with('polka', 'product', 'amountType')->findOrFail($id);

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
