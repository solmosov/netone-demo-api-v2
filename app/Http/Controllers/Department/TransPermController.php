<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransPermResource;
use App\Models\TransPerm;

class TransPermController extends Controller
{
    public function index()
    {
        $data =  TransPerm::all();
        return response()->json([
            'data' => TransPermResource::collection($data),
        ]);
    }
    public function show(string $id)
    {
        $data = TransPerm::findOrFail($id);

        return response()->json([
            'data' => TransPermResource::make($data),
        ]);
    }
}
