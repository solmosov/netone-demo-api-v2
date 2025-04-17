<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductionPlanRequest;
use App\Http\Requests\UpdateProductionPlanRequest;
use App\Models\ProductionPlan;
use App\Services\Production\ProductionPlanService;
use Illuminate\Support\Facades\Gate;

class ProductionPlanController extends Controller
{
    public function __construct(protected ProductionPlanService $productionPlanService) {}

    public function index() {}


    public function store(StoreProductionPlanRequest $request)
    {
        // Gate
        Gate::authorize('create', ProductionPlan::class);

        $result = $this->productionPlanService->create($request->validated());

        return response()->json([
            'message' => $result['message']
        ]);
    }


    public function show(ProductionPlan $productionPlan)
    {
        //
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
