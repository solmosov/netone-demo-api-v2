<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonCategoryRequest;
use App\Http\Requests\UpdatePersonCategoryRequest;
use App\Http\Resources\PersonCategoryResource;
use App\Models\PersonCategory;
use App\Services\Category\PersonCategoryService;
use Illuminate\Support\Facades\Gate;

class PersonCategoryController extends Controller
{
    public function __construct(
        protected PersonCategoryService $personCategoryService
    ) {}

    public function index()
    {
        // Gate
        Gate::authorize('viewAny', PersonCategory::class);

        $result = $this->personCategoryService->findAll();

        return response()->json([
            'data' => PersonCategoryResource::collection($result)
        ]);
    }


    public function store(StorePersonCategoryRequest $request)
    {
        // Gate
        Gate::authorize('create', PersonCategory::class);

        $result = $this->personCategoryService->create($request->validated());

        return response()->json([
            'message' => "{$result->name} - kategoriya yaratildi",
            'data' => PersonCategoryResource::make($result)
        ], 201);
    }


    public function show(string $id)
    {
        // Gate
        Gate::authorize('view', PersonCategory::class);

        $result = $this->personCategoryService->findOne($id);

        return response()->json([
            'data' => PersonCategoryResource::make($result)
        ]);
    }


    public function update(UpdatePersonCategoryRequest $request, string $id)
    {
        // Gate
        Gate::authorize('update', PersonCategory::class);

        $result = $this->personCategoryService->update($request->validated(), $id);


        return response()->json([
            'message' => "{$result->name} - kategoriya yangilandi",
            'data' => PersonCategoryResource::make($result)
        ]);
    }


    public function destroy(string $id)
    {
        // Gate
        Gate::authorize('delete', PersonCategory::class);

        $result = $this->personCategoryService->delete($id);

        return response()->json([
            'message' => "{$result->name} - kategoriya o'chirildi",
            'data' => PersonCategoryResource::make($result)
        ]);
    }
}
