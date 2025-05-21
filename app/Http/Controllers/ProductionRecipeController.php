<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductionRecipeRequest;
use App\Http\Requests\UpdateProductionRecipeItemRequest;
use App\Http\Requests\UpdateProductionRecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Models\ProductionRecipe;
use App\Services\AmountConverterService;
use App\Services\Production\ProductionRecipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ProductionRecipeController extends Controller
{
    public function __construct(
        protected AmountConverterService $amountConverter,
        protected ProductionRecipeService $productionRecipeService
    ) {}

    public function index(): JsonResponse
    {
        // Gate
        Gate::authorize('viewAny', ProductionRecipe::class);

        $data = $this->productionRecipeService->findAll();

        return response()->json([
            'data' => RecipeResource::collection($data)
        ]);
    }


    public function store(StoreProductionRecipeRequest $request)
    {
        // Gate
        Gate::authorize('create', ProductionRecipe::class);

        $data = $this->productionRecipeService->create($request->validated());

        return response()->json([
            "message" => "Yangi retsept qo'shildi",
            "data" => $data
        ], 201);
    }


    public function show(string $id): JsonResponse
    {
        // Gate
        Gate::authorize('view', ProductionRecipe::class);

        $productionRecipe = $this->productionRecipeService->findOne((int) $id);

        return response()->json([
            'data' => RecipeResource::make($productionRecipe)
        ]);
    }


    public function update(UpdateProductionRecipeRequest $request, string $id)
    {
        // Gate
        Gate::authorize('update', ProductionRecipe::class);

        $data = $this->productionRecipeService->update($request->validated(), (int)$id);

        return response()->json([
            'message' => "Retsept yangilandi",
            'data' => $data,
        ]);
    }

    public function updateItem(UpdateProductionRecipeItemRequest $request, string $id)
    {
        // Gate
        Gate::authorize('update', ProductionRecipe::class);

        $data = $this->productionRecipeService->updateItem($request->validated(), (int) $id);

        return [
            'message' => 'Retsept biri yangilandi',
            'data' => $data,
        ];
    }


    public function destroy(string $id)
    {
        // Gate
        Gate::authorize('delete', ProductionRecipe::class);

        $data = $this->productionRecipeService->delete((int)$id);

        return response()->json([
            'message' => "{$data->name} nomli retsept o'chirildi",
            'data'  => $data,
        ]);
    }

    public function destroyItem(string $id)
    {
        // Gate
        Gate::authorize('delete', ProductionRecipe::class);

        $data = $this->productionRecipeService->deleteItem((int) $id);

        return response()->json([
            'message' => "Retsept mahsuloti o'chirildi",
            'data'  => $data,
        ]);
    }
}
