<?php

namespace App\Http\Controllers;

use App\Models\ProducerProductTrans;
use App\Http\Requests\StoreProducerProductTransRequest;
use App\Http\Requests\UpdateProducerProductTransRequest;
use App\Services\ProductTransfer\ProducerProductTransService;

class ProducerProductTransController extends Controller
{
    public function __construct(
        protected ProducerProductTransService $producerProductTransService
    ) {}

    public function index()
    {
        //
    }


    public function store(StoreProducerProductTransRequest $request)
    {
        $this->producerProductTransService->create($request->validated());
    }


    public function show(ProducerProductTrans $producerProductTrans)
    {
        //
    }
}
