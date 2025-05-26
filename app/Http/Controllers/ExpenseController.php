<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetExpenseRequest;
use App\Models\Expense;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Services\Expense\ExpenseService;
use Illuminate\Support\Facades\Gate;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseService $expenseService
    ) {}

    public function index(GetExpenseRequest $request)
    {
        // Gate
        Gate::authorize('viewAny', Expense::class);

        $data = $this->expenseService->findAll($request->validated());

        return response()->json([
            "data" => ExpenseResource::collection($data),
        ]);
    }


    public function store(StoreExpenseRequest $request)
    {
        // Gate
        Gate::authorize('create', Expense::class);

        $newExpense = $this->expenseService->create($request->validated());

        return response()->json([
            "message" => "{$newExpense->name} - xarajat turi qo'shildi!",
            "data" => ExpenseResource::make($newExpense),
        ], 201);
    }


    public function show(string $id)
    {
        // Gate
        Gate::authorize('view', Expense::class);

        $data = $this->expenseService->findOne($id);

        return response()->json([
            "data" => ExpenseResource::make($data),
        ]);
    }


    public function update(UpdateExpenseRequest $request, string $id)
    {
        // Gate
        Gate::authorize('update', Expense::class);

        $updateExpense = $this->expenseService->update($request->validated(), $id);

        return response()->json([
            "message" => "{$updateExpense->name} - xarajat turi yangilandi",
            "data" => ExpenseResource::make($updateExpense),
        ]);
    }


    public function destroy(Expense $expense)
    {
        // Gate
        Gate::authorize('delete', Expense::class);

        $expense->delete();

        return response()->json([
            "message" => "Xarajat turi o'chirildi",
            "data" => ExpenseResource::make($expense),
        ]);
    }
}
