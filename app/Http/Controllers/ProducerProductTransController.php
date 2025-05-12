<?php

namespace App\Http\Controllers;

use App\Models\ProducerProductTrans;
use App\Http\Requests\StoreProducerSendProductToDepartTransRequest;
use App\Http\Requests\UpdateProducerProductTransRequest;
use App\Http\Resources\ProducerProductTransResource;
use App\Policies\ProducerProductTransPolicy;
use App\Services\ProductTransfer\ProducerProductTransService;
use Illuminate\Support\Facades\Gate;

class ProducerProductTransController extends Controller
{
    public function __construct(
        protected ProducerProductTransService $producerProductTransService
    ) {}

    public function index()
    {
        $data = $this->producerProductTransService->findAll();

        return response()->json([
            'data' => ProducerProductTransResource::collection($data)
        ]);
    }


    public function sendProductToDepart(StoreProducerSendProductToDepartTransRequest $request)
    {
        // Gate
        Gate::authorize('sendProductToDepart', ProducerProductTrans::class);

        $data = $this->producerProductTransService->sendProductToDepart($request->validated());

        return response()->json([
            'message' => '',
            'data' => ProducerProductTransResource::make($data)
        ]);
    }


    public function show(ProducerProductTrans $producerProductTrans)
    {
        //
    }
}
