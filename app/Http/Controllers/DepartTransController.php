<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartTransRequest;
use App\Http\Requests\UpdateDepartTransRequest;
use App\Models\DepartTrans;

class DepartTransController extends Controller
{
    public function index()
    {
        //
    }


    public function store(StoreDepartTransRequest $request)
    {
        dd($request->validated());
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
