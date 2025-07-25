<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidDataException;
use App\Exceptions\ServerErrorException;
use App\Http\Requests\GetOrderRequest;
use App\Http\Requests\StoreOrderPrintingWithExistsRecipe;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\StoreSalePosOrderRequest;
use App\Http\Requests\StoreOrderPrintingOrderRequest;
use App\Http\Requests\UpdateOrderAddProductRequest;
use App\Http\Requests\UpdateOrderCompletedRequest;
use App\Http\Requests\UpdateOrderSubmittedRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderShowResource;
use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(GetOrderRequest $request): JsonResponse
    {
        // Gate
        Gate::authorize('viewAny', Order::class);

        // Fetch Date
        $result = $this->orderService->findAll($request->validated());

        // Response
        return response()->json([
            'data' => OrderResource::collection($result['data']),
            'totals' => $result['totals']
        ]);
    }


    /**
     * @throws ServerErrorException
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        // Gate
        Gate::authorize('create', Order::class);

        $result = $this->orderService->create($request->validated());

        return response()->json($result);
    }

    public function storeSalePos(StoreSalePosOrderRequest $request)
    {
        // Gate
        Gate::authorize('create', Order::class);

        $result = $this->orderService->createSalePos($request->validated());

        return response()->json($result);
    }

    public function storePrintingOrder(StoreOrderPrintingOrderRequest $request)
    {
        // Gate
        Gate::authorize('create', Order::class);

        $result = $this->orderService->createPrintingOrder($request->validated());

        return response()->json([
            'message' => "Yangi retsept va yangi buyurtma yaratildi, bo'limlarga topshiriqlar yuborildi. Buyurtmaning holati yangi",
            'data' => $result
        ]);
    }

    public function storePrintingWithExistsRecipe(StoreOrderPrintingWithExistsRecipe $request)
    {
        //
    }


    public function show(string $id): JsonResponse
    {
        // Gate
        Gate::authorize('view', Order::class);

        $result = $this->orderService->findOne((int) $id);

        return response()->json([
            'data' => OrderShowResource::make($result['data'])
        ]);
    }

    public function confirm(string $id): JsonResponse
    {
        // Gate
        Gate::authorize('confirm', Order::class);

        $result = $this->orderService->confirm($id);

        return response()->json($result);
    }

    /**
     * @throws ServerErrorException
     */
    public function addProduct(UpdateOrderAddProductRequest $request, string $id): JsonResponse
    {
        Gate::authorize('addProduct', Order::class);

        $result = $this->orderService->addProduct($request->validated(), (int) $id);

        return response()->json($result);
    }

    /**
     * @throws ServerErrorException
     * @throws InvalidDataException
     */
    public function completed(UpdateOrderCompletedRequest $request, string $id): JsonResponse
    {
        // Gate
        Gate::authorize('completed', Order::class);

        $result = $this->orderService->completed($request->validated(), $id);

        return response()->json($result);
    }

    /**
     * @throws ServerErrorException
     */
    public function submitted(UpdateOrderSubmittedRequest $request, string $id): JsonResponse
    {
        // Gate
        Gate::authorize('submit', Order::class);

        $result = $this->orderService->submit($request->validated(), (int) $id);

        return response()->json($result);
    }
}
