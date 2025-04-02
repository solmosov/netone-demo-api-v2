<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartStatusRequest;
use App\Http\Requests\UpdateDepartStatusRequest;
use App\Http\Resources\DepartStatusResource;
use App\Models\Department\DepartStatus;
use App\Services\Department\DepartStatusService;
use Illuminate\Support\Facades\Gate;

class DepartStatusController extends Controller
{
    public function __construct(
        protected DepartStatusService $departStatusService
    ) {}

    public function index()
    {
        // Gate
        Gate::authorize('viewAny', DepartStatus::class);

        $result = $this->departStatusService->findAll();

        return [
            'data' => DepartStatusResource::make($result['data'])
        ];
    }


    public function store(StoreDepartStatusRequest $request)
    {
        // Gate
        Gate::authorize('create', DepartStatus::class);

        $result = $this->departStatusService->create($request->validated());

        return response()->json([
            'message' => $result['message'],
            'data' => DepartStatusResource::make($result['data'])
        ]);
    }


    public function show(string $id)
    {
        // Gate
        Gate::authorize('view', DepartStatus::class);

        $result = $this->departStatusService->findOne((int) $id);

        return [
            'data' => DepartStatusResource::make($result['data'])
        ];
    }


    public function update(UpdateDepartStatusRequest $request, string $id)
    {
        // Gate
        Gate::authorize('update', DepartStatus::class);

        $result = $this->departStatusService->update($request->validated(), (int) $id);

        return [
            'message' => $result['message'],
            'data' => DepartStatusResource::make($result['data'])
        ];
    }


    public function destroy(string $id)
    {
        // Gate
        Gate::authorize('delete', DepartStatus::class);

        $result = $this->departStatusService->delete((int) $id);

        return [
            'message' => $result['message']
        ];
    }
}
