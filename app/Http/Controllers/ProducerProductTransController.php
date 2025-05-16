<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProducerProductTransReceiveFromDepartRequest;
use App\Models\ProducerProductTrans;
use App\Http\Requests\StoreProducerSendProductToDepartTransRequest;
use App\Http\Resources\ProducerProductTransResource;
use App\Http\Resources\ProducerProductTransShowResource;
use App\Services\ProductTransfer\ProducerProductTransService;
use Illuminate\Support\Facades\Gate;

class ProducerProductTransController extends Controller
{
    public function __construct(
        protected ProducerProductTransService $producerProductTransService
    ) {}

    public function index()
    {
        // Gate
        Gate::authorize('viewAnyProducer', ProducerProductTrans::class);

        $data = $this->producerProductTransService->findAll();

        return response()->json([
            'data' => ProducerProductTransResource::collection($data)
        ]);
    }

    public function indexReceive()
    {
        // Gate
        Gate::authorize('viewAnyProducer', ProducerProductTrans::class);

        $data = $this->producerProductTransService->findAllReceive();

        return response()->json([
            'data' => ProducerProductTransResource::collection($data)
        ]);
    }

    public function sendProductToDepart(StoreProducerSendProductToDepartTransRequest $request)
    {
        // Gate
        Gate::authorize('sendProductToDepart', ProducerProductTrans::class);

        $this->producerProductTransService->sendProductToDepart($request->validated());

        return response()->json([
            'message' => 'Yuk muvaffaqiyatli yuborildi',
        ], 201);
    }

    public function receiveProductFromDepart(string $id, StoreProducerProductTransReceiveFromDepartRequest $request)
    {
        $this->producerProductTransService->receiveProductFromDepart((int) $id, $request->validated());

        return response()->json([
            'message' => "Yuk muvaffaqiyatli qabul qilindi",
        ], 201);
    }


    public function show(string $id)
    {
        // Gate
        Gate::authorize('viewProducer', ProducerProductTrans::class);

        $data = $this->producerProductTransService->findOne((int) $id);

        return response()->json([
            'data' => ProducerProductTransShowResource::make($data)
        ]);
    }
}
