<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartAssignPermRequest;
use App\Http\Requests\StoreDepartRequest;
use App\Http\Requests\UpdateDepartRequest;
use App\Http\Resources\DepartResource;
use App\Http\Resources\DepartShowResource;
use App\Models\Depart;
use App\Services\Department\DepartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class DepartController extends Controller
{
    public function __construct(
        protected DepartService $departService
    ) {}

    public function index(): JsonResponse
    {
        // Gate
        Gate::authorize('viewAny', Depart::class);

        $result = $this->departService->findAll();

        return response()->json([
            'data' => DepartResource::collection($result['data']),
        ]);
    }


    public function store(StoreDepartRequest $request): JsonResponse
    {
        // Gate
        Gate::authorize('create', Depart::class);

        $result = $this->departService->create($request->validated());

        return response()->json([
            'message' => $result['message'],
        ], 201);
    }

    public function assignPerm(StoreDepartAssignPermRequest $request): JsonResponse
    {
        $result = $this->departService->assignPerm($request->validated());

        return response()->json([
            'message' => $result['message'],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        // Gate
        Gate::authorize('view', Depart::class);

        $result = $this->departService->findOne((int) $id);

        return response()->json([
            'data' => DepartShowResource::make($result['data']),
        ]);
    }


    public function update(UpdateDepartRequest $request, string $id)
    {
        // Gate
        Gate::authorize('update', Depart::class);

        $result = $this->departService->update($request->validated(), (int) $id);

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }


    public function destroy(string $id)
    {
        // Gate
        Gate::authorize('delete', Depart::class);

        $result = $this->departService->delete((int) $id);

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }
}
