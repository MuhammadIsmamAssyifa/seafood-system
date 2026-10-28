<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderBatch;
use App\Services\OrderBatchService;

class OrderBatchController extends Controller
{
    public function __construct(
        protected OrderBatchService $orderBatchService
    ) {}


    public function submit(OrderBatch $orderBatch)
    {
        $batch = $this->orderBatchService
            ->submit($orderBatch);


        return response()->json([
            'message' => 'Pesanan berhasil dikirim ke dapur.',
            'data' => $batch,
        ]);
    }
}
