<?php

namespace App\Http\Controllers;

use App\Exceptions\ServerErrorException;
use App\Http\Requests\FinishProductionProcessRequest;
use App\Http\Requests\StoreProductionProcessRequest;
use App\Http\Resources\ProductionProcessResource;
use App\Http\Resources\ProductionProcessShowResource;
use App\Models\Product;
use App\Models\ProductionProcess;
use App\Models\ProductStock;
use App\Models\ReceiveProduct;
use App\Models\ReceiveProductDetail;
use App\Models\Status;
use App\Services\Production\ProductionProcessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductionProcessController extends Controller
{
    public function __construct(
        protected ProductionProcessService $productionProcessService,
    )
    {
    }

    public function index(): JsonResponse
    {
        $data = $this->productionProcessService->findAll();

        return response()->json([
            'data' => ProductionProcessResource::collection($data),
        ]);
    }


    /**
     * @throws ServerErrorException
     */
    public function store(StoreProductionProcessRequest $request): JsonResponse
    {
        $result = $this->productionProcessService->create($request->validated());

        return response()->json([
            'message' => $result['message'],
        ], 201);
    }


    public function show(string $id): JsonResponse
    {
        $data = ProductionProcess::with(
            'depart',
            'productionRecipe.recipeItems',
            'processItems',
            'status'
        )->findOrFail($id);

        return response()->json([
            'data' => ProductionProcessShowResource::make($data)
        ]);
    }


    public function finish(FinishProductionProcessRequest $request, string $id)
    {
        $productionProcess = ProductionProcess::with('productionRecipe', 'processItems')->findOrFail($id);

        // Status productionCompleted
        $statusCurrent = Status::findOrFail($productionProcess->status_id);

        if ($statusCurrent->code !== 'productionProcess') {
            if ($statusCurrent->code === 'productionCancel') {
                return $this->mainErrRes('Bu ishlab chiqarish jarayoni allaqachon bekor qilingan');
            } else if ($statusCurrent->code === 'productionStopped') {
                return $this->mainErrRes("Bu ishlab chiqarish jarayoni allaqachon to'xtatilgan");
            } else if ($statusCurrent->code === 'productionCompleted') {
                return $this->mainErrRes("Bu ishlab chiqarish jarayoni allaqachon tayyorlangan");
            }

            return $this->mainErrRes("Bu ishlab chiqarish jarayonini tugallab bo'lmaydi");
        }

        // Status productionCompleted
        $statusProductionCompleted = Status::where('code', 'productionCompleted')->firstOrFail();

        $stock = ProductStock::where('product_id', $productionProcess->productionRecipe->out_product_id)->firstOrFail();
        $outProduct = Product::findOrFail($productionProcess->productionRecipe->out_product_id);

        DB::beginTransaction();

        try {
            $productionProcess->status_id = $statusProductionCompleted->id;
            $productionProcess->out_amount = $request->validated('total_amount');

            // Change stock
            $stock->increment('amount', $productionProcess->out_amount);

            $productionProcess->save();

            // Cost Price
            $costPrice = 0;

            foreach ($productionProcess->processItems as $item) {
                // Get Last Receive Product
                // $lastReceive = ReceiveProductDetail::where('product_id', $item->product_id)->latest()->first();
                // if ($lastReceive) {
                //     $costPrice += $lastReceive->price * $item->amount;
                // }

                $product = Product::findOrFail($item->product_id);
                $costPrice += $product->cost_price * $item->amount;
            }

            $costPrice /= $request->validated('total_amount');

            // Set Cost Price
            $productionProcess->cost_price = $costPrice;
            $productionProcess->save();

            // Set Cost Price
            $outProduct->update(['cost_price' => $costPrice]);
            $outProduct->save();

            DB::commit();

            return response()->json([
                'message' => "$id. Ishlab chiqarish jarayoni yakunlandi"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverError($e);
        }
    }


    public function cancel(string $id): JsonResponse
    {
        $data = ProductionProcess::with(
            'productionRecipe',
            'processItems',
            'status'
        )->findOrFail($id);

        // Status productionProcess
        $statusProductionProcess = Status::where('code', 'productionProcess')
            ->where('id', $data->status_id)
            ->exists();

        if (!$statusProductionProcess) return $this->mainErrRes("Bu ishlab chiqarish jarayonini bekor qilib bo'lmaydi");

        // Status productionCancel
        $statusProductionCancel = Status::where('code', 'productionCancel')->firstOrFail();

        $data->status_id = $statusProductionCancel->id;

        $data->save();

        return response()->json([
            'message' => "#$data->id. Ishlab chiqarish jarayoni bekor qilindi",
            'data' => [
                'id' => $data->id,
            ],
        ]);
    }
}
