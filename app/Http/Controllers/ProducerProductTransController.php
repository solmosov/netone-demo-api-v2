<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProducerProductTransReceiveFromDepartRequest;
use App\Models\ProducerProductTrans;
use App\Http\Requests\StoreProducerSendProductToDepartTransRequest;
use App\Http\Resources\ProducerProductTransResource;
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

    public function receiveProductFromDepart(string $id, StoreProducerProductTransReceiveFromDepartRequest $request)
    {
        $data = $this->producerProductTransService->receiveProductFromDepart((int) $id, $request->validated());

        return [
            'message' => "Yuk muvaffaqiyatli qabul qilindi",
            'data' => $data,
        ];
    }


    public function show(string $id)
    {
        //
    }
}
