<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductionPlanRequest;
use App\Http\Requests\UpdateProductionPlanRequest;
use App\Http\Resources\ProductionPlanResource;
use App\Http\Resources\ProductionPlanShowResource;
use App\Models\ProductionPlan;
use App\Services\Production\ProductionPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ProductionPlanController extends Controller
{
    public function __construct(protected ProductionPlanService $productionPlanService) {}

    public function index(): JsonResponse
    {
        $result = $this->productionPlanService->findAll();

        return response()->json([
            'data' => ProductionPlanResource::collection($result['data'])
        ]);
    }


    public function store(StoreProductionPlanRequest $request): JsonResponse
    {
        // Gate
        Gate::authorize('create', ProductionPlan::class);

        $result = $this->productionPlanService->create($request->validated());

        return response()->json([
            'message' => $result['message']
        ], $result['code']);
    }


    public function show(string $id): JsonResponse
    {
        // Gate
        Gate::authorize('view', ProductionPlan::class);

        $result = $this->productionPlanService->findOne($id);

        return response()->json([
            'data' => ProductionPlanShowResource::make($result['data']),
        ]);
    }


    public function update(UpdateProductionPlanRequest $request, ProductionPlan $productionPlan)
    {
        //
    }


    public function destroy(ProductionPlan $productionPlan)
    {
        //
    }
}
