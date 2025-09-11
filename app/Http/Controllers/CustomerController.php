<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetCustomerRequest;
use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Services\Customer\CustomerService;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function index(GetCustomerRequest $request)
    {
        // Gate
        Gate::authorize('viewAny', Customer::class);

        $result = $this->customerService->findAll($request->validated());

        return response()->json([
            "data" => CustomerResource::collection($result['data']),
            "total_balance" => (float) $result['total_balance'],
        ]);
    }


    public function store(StoreCustomerRequest $request)
    {
        // Gate
        Gate::authorize('create', Customer::class);

        $result = $this->customerService->create($request->validated());

        return response()->json([
            'message' => "Yangi mijoz muvaffaqiyatli qo'shildi!",
            'data' => CustomerResource::make($result),
        ], 201);
    }


    public function show(string $id)
    {
        // Gate
        Gate::authorize('view', Customer::class);

        $result = $this->customerService->findOne((int) $id);

        return response()->json([
            "data" => CustomerResource::make($result),
        ]);
    }


    public function update(UpdateCustomerRequest $request, string $id)
    {
        // Gate
        Gate::authorize('update', Customer::class);


        // Update
        $result = $this->customerService->update((int)$id, $request->validated());


        return response()->json([
            'message' => "Mijoz muvaffaqiyatli tahrirlandi!",
            'data' => CustomerResource::make($result)
        ]);
    }


    public function destroy(string $id)
    {
        // Gate
        Gate::authorize('delete', Customer::class);

        // Delete Customer
        $result = $this->customerService->delete((int) $id);

        return response()->json([
            'message' => "Mijoz muvaffaqiyatli o'chirildi!",
            'data' => CustomerResource::make($result)
        ]);
    }
}
