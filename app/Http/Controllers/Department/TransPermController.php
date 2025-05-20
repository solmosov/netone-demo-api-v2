<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransPermResource;
use App\Services\Department\DepartTransPermService;

class TransPermController extends Controller
{
    public function __construct(
        protected DepartTransPermService $departTransPermService,
    ) {}

    public function index()
    {
        $data =  $this->departTransPermService->findAll();
        return response()->json([
            'data' => TransPermResource::collection($data),
        ]);
    }
    public function show(string $id)
    {
        $data = $this->departTransPermService->findById($id);

        return response()->json([
            'data' => TransPermResource::make($data),
        ]);
    }

    public function showByCode(string $code)
    {
        $data = $this->departTransPermService->findByCode($code);

        return response()->json([
            'data' => TransPermResource::make($data),
        ]);
    }
}
