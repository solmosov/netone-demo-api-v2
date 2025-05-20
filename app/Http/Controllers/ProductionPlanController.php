<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetPlanItemsByDepartRequest;
use App\Http\Requests\StoreProductionPlanRequest;
use App\Http\Requests\UpdateProductionPlanItemCompleteRequst;
use App\Http\Requests\UpdateProductionPlanItemReceiveRequest;
use App\Http\Requests\UpdateProductionPlanRequest;
use App\Http\Resources\PlanItemByDepartResource;
use App\Http\Resources\ProductionPlanResource;
use App\Http\Resources\ProductionPlanShowResource;
use App\Models\ProductionPlan;
use App\Services\Production\ProductionPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ProductionPlanController extends Controller
{
    public function __construct(protected ProductionPlanService $productionPlanService)
    {
    }

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


    public function show(string $id)
    {
        // Gate
        Gate::authorize('view', ProductionPlan::class);

        $result = $this->productionPlanService->findOne($id);

        return response()->json([
            'data' => ProductionPlanShowResource::make($result['data']),
        ]);
    }

    public function showPlanItemsByDepart(GetPlanItemsByDepartRequest $request): JsonResponse
    {
        $data = $this->productionPlanService->findPlanItemsByDepart($request->validated('depart_id'));

        return response()->json([
            'data' => PlanItemByDepartResource::collection($data),
        ]);
    }


    public function update(UpdateProductionPlanRequest $request, ProductionPlan $productionPlan)
    {
        //
    }

    public function receivePlanItem(UpdateProductionPlanItemReceiveRequest $request, int $planItemId): JsonResponse
    {
        $result = $this->productionPlanService->receivePlanItem($planItemId);

        return response()->json($result);
    }

    public function completedPlanItem(UpdateProductionPlanItemCompleteRequst $request, int $planItemId): JsonResponse
    {
        $result = $this->productionPlanService->completedPlanItem($planItemId);

        return response()->json($result);
    }

    public function destroy(ProductionPlan $productionPlan)
    {
        //
    }

    public function finish(string $id): JsonResponse
    {
        $result = $this->productionPlanService->finish((int)$id);

        return response()->json([
            'message' => $result['message']
        ]);
    }
}
