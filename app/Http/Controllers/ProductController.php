<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index()
    {
        // Gate
        Gate::authorize('viewAny', Product::class);

        $result = $this->productService->findAll();

        return response()->json([
            "data" => ProductResource::collection($result['data']),
        ]);
    }


    public function store(StoreProductRequest $request): JsonResponse
    {
        // Gate
        Gate::authorize('create', Product::class);

        $newProduct = $this->productService->create($request->validated());

        return response()->json([
            "message" => "Yangi mahsulot muvaffaqiyatli qo'shildi!",
            "data" => ProductResource::make($newProduct),
        ], 201);
    }


    public function show(string $id): JsonResponse
    {
        // Gate
        Gate::authorize('view', Product::class);

        $product = $this->productService->findOne((int) $id);

        return response()->json([
            "data" => ProductResource::make($product),
        ]);
    }


    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        // Gate
        Gate::authorize('update', Product::class);

        $product = $this->productService->update($request->validated(), (int) $id);

        return response()->json([
            "message" => "`{$product->name}` mahsulot tahrirlandi!",
            "data" => ProductResource::make($product),
        ]);
    }


    public function destroy(string $id): JsonResponse
    {
        // Gate
        Gate::authorize('delete', Product::class);

        $product = $this->productService->delete($id);;

        return response()->json([
            "message" => "`{$product->name}` mahsulot o'chirildi!",
            "data" => ProductResource::make($product),
        ]);
    }
}
