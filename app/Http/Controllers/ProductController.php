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


    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        // Gate
        Gate::authorize('update', Product::class);

        // Check Exist
        if ($request->validated('name')) {
            $nameIsExists = Product::where('name', $request->validated('name'))
                ->where('id', '<>', $product->id)
                ->exists();
            if ($nameIsExists) abort(422, "Bu mahsulot nomi allaqachon mavjud");
        }

        // Update Data
        $name = $request->validated('name');
        $sale_price = $request->validated('sale_price');
        $price_amount_type_id = $request->validated('price_amount_type_id');

        if ($name) {
            $product->name = $request->validated('name');
        }
        if ($sale_price) {
            $product->sale_price = $request->validated('sale_price');
        }
        if ($price_amount_type_id) {
            $product->price_amount_type_id = $request->validated('price_amount_type_id');
        }

        $product->save();

        return response()->json([
            "message" => "Mahsulot muvaffaqiyatli tahrirlandi!",
            "data" => ProductResource::make($product),
        ]);
    }


    public function destroy(Product $product): JsonResponse
    {
        // Gate
        Gate::authorize('delete', Product::class);

        $product->delete();

        return response()->json([
            "message" => "Mahsulot muvaffaqiyatli o'chirildi!",
            "data" => ProductResource::make($product),
        ]);
    }
}
