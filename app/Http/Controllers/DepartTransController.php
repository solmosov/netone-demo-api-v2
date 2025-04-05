<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartTransRequest;
use App\Http\Requests\UpdateDepartTransRequest;
use App\Models\DepartTrans;
use App\Services\TransferProduct\DepartTransService;

class DepartTransController extends Controller
{
    public function __construct(
        protected DepartTransService $departTransService,
    ) {}

    public function index()
    {
        //
    }


    public function store(StoreDepartTransRequest $request)
    {
        $result = $this->departTransService->create($request->validated());

        return response()->json([
            'message' => $result['message'],
        ]);
    }


    public function show(DepartTrans $departTrans)
    {
        //
    }


    public function update(UpdateDepartTransRequest $request, DepartTrans $departTrans)
    {
        //
    }


    public function destroy(DepartTrans $departTrans)
    {
        //
    }
}
