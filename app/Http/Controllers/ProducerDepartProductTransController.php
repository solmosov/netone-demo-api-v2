<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProducerReceiveProductFromProducerRequest;
use App\Http\Requests\StoreProducerSendProductToProducerTransRequest;
use App\Http\Resources\ProducerProductTransResource;
use App\Http\Resources\ProducerProductTransShowResource;
use App\Models\ProducerProductTrans;
use App\Services\ProductTransfer\ProducerDepartProductTransService;
use Illuminate\Support\Facades\Gate;

class ProducerDepartProductTransController extends Controller
{
    public function __construct(
        protected ProducerDepartProductTransService $producerDepartProductTransService
    ) {}

    public function index()
    {
        // Gate
        Gate::authorize('viewAnyDepart', ProducerProductTrans::class);

        $data = $this->producerDepartProductTransService->findAll();

        return response()->json([
            'data' => ProducerProductTransResource::collection($data)
        ]);
    }
    public function indexReceive()
    {
        // Gate
        Gate::authorize('viewAnyDepart', ProducerProductTrans::class);

        $data = $this->producerDepartProductTransService->findAllReceive();

        return response()->json([
            'data' => ProducerProductTransResource::collection($data)
        ]);
    }


    public function sendProductToProducer(StoreProducerSendProductToProducerTransRequest $request)
    {
        // Gate
        Gate::authorize('sendProductToProducer', ProducerProductTrans::class);

        $this->producerDepartProductTransService->create($request->validated());

        return response()->json([
            'message' => "Mahsulot muvaffaqiyatli yuborildi",
        ], 201);
    }

    public function receiveProductFromProducer(string $id, StoreProducerReceiveProductFromProducerRequest $request)
    {
        $this->producerDepartProductTransService->receive($id, $request->validated());

        return response()->json([
            'message' => "Mahsulot muvaffaqiyatli qabul qilindi"
        ], 201);
    }


    public function show(string $id)
    {
        $data = $this->producerDepartProductTransService->findOne((int) $id);

        return response()->json([
            'data' =>  ProducerProductTransShowResource::make($data),
        ]);
    }
}
