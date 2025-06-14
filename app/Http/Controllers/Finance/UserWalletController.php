<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserWalletRequest;
use App\Http\Resources\UserWalletResource;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Services\Wallet\UserWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class UserWalletController extends Controller
{
    public function __construct(
        protected UserWalletService $userWalletService
    ) {}

    public function index(): JsonResponse
    {
        // Gate
        Gate::authorize('viewAny', UserWallet::class);

        $result = $this->userWalletService->findAll();

        return response()->json([
            'data' => UserWalletResource::collection($result['data']),
            'total_count' => (int) $result['total_count']
        ]);
    }

    public function store(StoreUserWalletRequest $request): JsonResponse
    {
        // Gate
        Gate::authorize('create', UserWallet::class);

        $result = $this->userWalletService->create($request->validated());

        return response()->json($result, 201);
    }

    public function show(string $id): JsonResponse
    {
        // Gate
        Gate::authorize('view', UserWallet::class);

        $result = $this->userWalletService->findOne((int)$id);

        return response()->json([
            'data' => UserWalletResource::make($result),
        ]);
    }
}
