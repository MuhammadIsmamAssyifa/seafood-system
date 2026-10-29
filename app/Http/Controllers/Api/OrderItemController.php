<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddOrderItemRequest;
use App\Http\Requests\UpdateOrderItemRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderItemService;

class OrderItemController extends Controller
{
    public function __construct(
        protected OrderItemService $orderItemService
    ) {}

    /**
     * Add item to order
     */
    public function store(
        AddOrderItemRequest $request,
        Order $order
    ) {

        $order = $this->orderItemService->addItem(
            $order,
            $request->validated()
        );

        return new OrderResource(
            $order->load('batches.items')
        );
    }

    public function update(
        UpdateOrderItemRequest $request,
        OrderItem $orderItem
    ): OrderResource {

        $order = $this->orderItemService->updateItem(
            $orderItem,
            $request->validated()
        );

        return new OrderResource(
            $order->load('batches.items')
        );
    }

    public function destroy(
        OrderItem $orderItem
    ): OrderResource {

        $order = $this->orderItemService->deleteItem(
            $orderItem
        );

        return new OrderResource(
            $order->load('batches.items')
        );
    }
}
