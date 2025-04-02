<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartStatusRequest;
use App\Http\Requests\UpdateDepartStatusRequest;
use App\Http\Resources\DepartStatusResource;
use App\Services\Department\DepartStatusService;

class DepartStatusController extends Controller
{
    public function __construct(
        protected DepartStatusService $departStatusService
    ) {}

    public function index()
    {
        $result = $this->departStatusService->findAll();

        return [
            'data' => DepartStatusResource::make($result['data'])
        ];
    }


    public function store(StoreDepartStatusRequest $request)
    {
        $result = $this->departStatusService->create($request->validated());

        return response()->json([
            'message' => $result['message'],
            'data' => DepartStatusResource::make($result['data'])
        ]);
    }


    public function show(string $id)
    {
        $result = $this->departStatusService->findOne((int) $id);

        return [
            'data' => DepartStatusResource::make($result['data'])
        ];
    }


    public function update(UpdateDepartStatusRequest $request, string $id)
    {
        $result = $this->departStatusService->update($request->validated(), (int) $id);

        return [
            'message' => $result['message'],
            'data' => DepartStatusResource::make($result['data'])
        ];
    }


    public function destroy(string $id)
    {
        $result = $this->departStatusService->delete((int) $id);

        return [
            'message' => $result['message']
        ];
    }
}
