<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartTransProcessReceiveRequest;
use App\Http\Requests\StoreDepartTransProcessRequest;
use App\Http\Resources\DepartTransProcessResource;
use App\Http\Resources\DepartTransProcessShowResource;
use App\Services\Department\DepartTransProcessService;
use Illuminate\Http\Request;

class DepartTransProcessController extends Controller
{

    public function __construct(
        protected DepartTransProcessService $departTransProcessService
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'trans_perm_id' => 'nullable|integer|exists:trans_perms,id',
            'trans_perm_code' => 'nullable|string|exists:trans_perms,code',
            'sender_depart_id' => 'nullable|integer|exists:departments,id',
            'receiver_depart_id' => 'nullable|integer|exists:departments,id',
            'sender_user_id' => 'nullable|integer|exists:users,id',
            'receiver_user_id' => 'nullable|integer|exists:users,id',
            'status_id' => 'nullable|integer|exists:statuses,id',
            'status_code' => 'nullable|string|exists:statuses,code',
        ]);
        
        $result = $this->departTransProcessService->findAll($validated);

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
