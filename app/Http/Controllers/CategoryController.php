<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetCategoryRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\Category\CategoryService;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function index(GetCategoryRequest $request)
    {
        // Gate
        Gate::authorize('viewAny', Category::class);

        $result = $this->categoryService->findAll($request->validated());

        return response()->json([
            'data' => CategoryResource::make($result['data'])
        ]);
    }


    public function store(StoreCategoryRequest $request)
    {
        // Gate
        Gate::authorize('create', Category::class);

        $newCategory = $this->categoryService->create($request->validated());

        return response()->json([
            'message' => "{$newCategory->name} - yangi kategoriya yaratildi",
            'data' => CategoryResource::make($newCategory),
        ]);
    }


    public function show(string $id)
    {
        // Gate
        Gate::authorize('view', Category::class);

        $category = $this->categoryService->findOne((int) $id);

        return response()->json([
            'data' => CategoryResource::make($category),
        ]);
    }


    public function update(UpdateCategoryRequest $request, string $id)
    {
        // Gate
        Gate::authorize('update', Category::class);

        $updateCategory = $this->categoryService->update($request->validated(), (int) $id);

        return response()->json([
            'message' => "{$updateCategory->name} - kategoriya tahrirlandi",
            'data' => CategoryResource::make($updateCategory),
        ]);
    }


    public function destroy(string $id)
    {
        // Gate
        Gate::authorize('delete', Category::class);

        $updateCategory = $this->categoryService->delete((int) $id);

        return response()->json([
            'message' => "{$updateCategory->name} - kategoriya o'chirildi",
            'data' => CategoryResource::make($updateCategory),
        ]);
    }
}
