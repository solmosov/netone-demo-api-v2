<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetCategoryRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\Category\CategoryService;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function index(GetCategoryRequest $request)
    {
        $result = $this->categoryService->findAll($request->validated());

        return response()->json([
            'data' => CategoryResource::make($result['data'])
        ]);
    }


    public function store(StoreCategoryRequest $request)
    {
        $newCategory = $this->categoryService->create($request->validated());

        return response()->json([
            'message' => "{$newCategory->name} - yangi katalok yaratildi",
            'data' => CategoryResource::make($newCategory),
        ]);
    }


    public function show(string $id)
    {
        $category = $this->categoryService->findOne((int) $id);

        return response()->json([
            'data' => CategoryResource::make($category),
        ]);
    }


    public function update(UpdateCategoryRequest $request, string $id)
    {
        $updateCategory = $this->categoryService->update($request->validated(), (int) $id);

        return response()->json([
            'message' => "{$updateCategory->name} - katalok tahrirlandi",
            'data' => CategoryResource::make($updateCategory),
        ]);
    }


    public function destroy(string $id)
    {
        $updateCategory = $this->categoryService->delete((int) $id);

        return response()->json([
            'message' => "{$updateCategory->name} - katalok o'chirildi",
            'data' => CategoryResource::make($updateCategory),
        ]);
    }
}
