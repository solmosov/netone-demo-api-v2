<?php

namespace App\Http\Controllers;

use App\Exceptions\ServerErrorException;
use App\Http\Requests\FinishProductionProcessRequest;
use App\Http\Requests\StoreProductionProcessRequest;
use App\Http\Resources\ProductionProcessResource;
use App\Http\Resources\ProductionProcessShowResource;
use App\Services\Production\ProductionProcessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionProcessController extends Controller
{
    public function __construct(
        protected ProductionProcessService $productionProcessService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'depart_id' => 'nullable|integer|exists:departs,id',
        ]);

        $data = $this->productionProcessService->findAll($validated['depart_id'] ?? null);

        return response()->json([
            'data' => ProductionProcessResource::collection($data),
        ]);
    }


    /**
     * @throws ServerErrorException
     */
    public function store(StoreProductionProcessRequest $request): JsonResponse
    {
        $result = $this->productionProcessService->create($request->validated());

        return response()->json([
            'message' => $result['message'],
        ], 201);
    }


    public function show(string $id): JsonResponse
    {
        $data = $this->productionProcessService->findOne((int)$id);

        return response()->json([
            'data' => ProductionProcessShowResource::make($data)
        ]);
    }


    public function finish(FinishProductionProcessRequest $request, string $id): JsonResponse
    {
        $result = $this->productionProcessService->finish($request->validated(), (int)$id);
        return response()->json([
            'message' => $result['message'],
        ]);
    }
}
