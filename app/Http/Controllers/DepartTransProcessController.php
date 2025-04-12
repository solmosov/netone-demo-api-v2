<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartTransProcessRequest;
use App\Models\DepartTransProcess;
use App\Services\Department\DepartTransProcessService;

class DepartTransProcessController extends Controller
{

    public function __construct(
        protected DepartTransProcessService $departTransProcessService
    ) {}

    public function index()
    {
        //
    }


    public function store(StoreDepartTransProcessRequest $request)
    {
        $result = $this->departTransProcessService->create($request->validated());

        return response()->json([
            'message' => $result['message']
        ]);
    }


    public function show(string $id)
    {
        //
    }
}
