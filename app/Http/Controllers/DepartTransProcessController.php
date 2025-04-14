<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartTransProcessReceiveRequest;
use App\Http\Requests\StoreDepartTransProcessRequest;
use App\Http\Resources\DepartTransProcessResource;
use App\Http\Resources\DepartTransProcessShowResource;
use App\Services\Department\DepartTransProcessService;

class DepartTransProcessController extends Controller
{

    public function __construct(
        protected DepartTransProcessService $departTransProcessService
    ) {}

    public function index()
    {
        $result = $this->departTransProcessService->findAll();

        return response()->json([
            'data' => DepartTransProcessResource::collection($result['data'])
        ]);
    }


    public function store(StoreDepartTransProcessRequest $request)
    {
        $result = $this->departTransProcessService->create($request->validated());

        return response()->json([
            'message' => $result['message']
        ], 201);
    }


    public function show(string $id)
    {
        $result = $this->departTransProcessService->findOne($id);

        return response()->json([
            'data' => DepartTransProcessShowResource::make($result['data'])
        ]);
    }

    public function receive(StoreDepartTransProcessReceiveRequest $request, string $id)
    {
        $result = $this->departTransProcessService->receive($request->validated(), (int) $id);

        return response()->json([
            'message' => $result['message']
        ], $result['code']);
    }
}
