<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProducerSendProductToProducerTransRequest;
use App\Http\Resources\ProducerProductTransResource;
use App\Models\ProducerProductTrans;
use App\Services\ProductTransfer\ProducerDepartProductTransService;
use Illuminate\Http\Request;
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


    public function sendProductToProducer(StoreProducerSendProductToProducerTransRequest $request)
    {
        // Gate
        Gate::authorize('sendProductToProducer', ProducerProductTrans::class);

        $data = $this->producerDepartProductTransService->create($request->validated());

        return response()->json([
            'message' => '',
            'data' => ProducerProductTransResource::make($data)
        ]);
    }


    public function show(string $id)
    {
        //
    }
}
