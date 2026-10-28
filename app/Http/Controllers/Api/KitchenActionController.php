<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderBatchResource;
use App\Http\Resources\OrderItemResource;
use App\Models\OrderBatch;
use App\Models\OrderItem;
use App\Services\KitchenService;

class KitchenActionController extends Controller
{
    public function __construct(
        protected KitchenService $kitchenService
    ) {}

    public function acceptBatch(OrderBatch $batch): OrderBatchResource
    {
        $updatedBatch = $this->kitchenService->acceptBatch($batch);

        return new OrderBatchResource($updatedBatch->load(['items', 'order']));
    }

    public function prepareItem(OrderItem $item): OrderItemResource
    {
        $updatedItem = $this->kitchenService->prepareItem($item);

        return new OrderItemResource($updatedItem);
    }

    public function readyItem(OrderItem $item): OrderItemResource
    {
        $updatedItem = $this->kitchenService->readyItem($item);

        return new OrderItemResource($updatedItem);
    }

    public function serveItem(OrderItem $item): OrderItemResource
    {
        $updatedItem = $this->kitchenService->serveItem($item);

        return new OrderItemResource($updatedItem);
    }
}
