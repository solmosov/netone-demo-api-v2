<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartPermissionRequest;
use App\Services\Department\DepartTransPermService;
use Illuminate\Http\Request;

class DepartPermissionController extends Controller
{
    public function __construct(protected DepartTransPermService $deptTransPermService) {}

    public function index()
    {
        //
    }


    public function store(StoreDepartPermissionRequest $request)
    {
        $result = $this->deptTransPermService->create($request->validated());

        return response()->json([
            'message' => $result['message'],
        ]);
    }


    public function show(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id)
    {
        //
    }
}
