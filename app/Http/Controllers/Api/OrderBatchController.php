<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderBatchResource;
use App\Models\OrderBatch;
use App\Services\OrderBatchService;

class OrderBatchController extends Controller
{
    public function __construct(
        protected OrderBatchService $orderBatchService
    ) {}

    public function submit(OrderBatch $orderBatch): OrderBatchResource
    {
        $batch = $this->orderBatchService->submit($orderBatch);

        return (new OrderBatchResource($batch->load(['items', 'order'])))
            ->additional(['message' => 'Pesanan berhasil dikirim ke dapur.']);
    }
}
