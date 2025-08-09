<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetDepartByTransPermRequest;
use App\Http\Requests\RemoveDepartAssignUserRequest;
use App\Http\Requests\StoreAssignNewUserRequest;
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

    public function indexByTransPerm(GetDepartByTransPermRequest $request): JsonResponse
    {
        // Gate
        Gate::authorize('viewAny', Depart::class);

        $result = $this->departService->findAllByTransPerm($request->validated());

        return response()->json([
            'data' => DepartResource::collection($result),
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
        // Gate
        Gate::authorize('assignPerm', Depart::class);

        $result = $this->departService->assignPerm($request->validated());

        return response()->json([
            'message' => $result['message'],
        ]);
    }

    public function assignNewUser(StoreAssignNewUserRequest $request)
    {
        // Gate
        Gate::authorize('create', Depart::class);

        $result = $this->departService->assignNewUser($request->validated());

        return response()->json([
            'message' => "{$result['attach_user']['name']} foydalanuvchi, {$result['depart']['name']} bo'limga qo'shildi",
        ], 201);
    }

    public function removeAssignUser(RemoveDepartAssignUserRequest $request)
    {
        // Gate
        Gate::authorize('delete', Depart::class);

        $result = $this->departService->removeAssignUser($request->validated());

        return response()->json([
            'message' => "{$result['attach_user']['name']} foydalanuvchi, {$result['depart']['name']} bo'limdan chiqarib yuborildi",
        ], 200);
    }

    public function show(string $id): JsonResponse
    {
        // Gate
        Gate::authorize('view', Depart::class);

        $result = $this->departService->findOne((int) $id);

        return response()->json([
            'data' => DepartShowResource::make($result),
        ]);
    }


    public function update(UpdateDepartRequest $request, string $id): JsonResponse
    {
        // Gate
        Gate::authorize('update', Depart::class);

        $result = $this->departService->update($request->validated(), (int) $id);

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }


    public function destroy(string $id): JsonResponse
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
