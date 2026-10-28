<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use App\Http\Resources\OrderResource;
use App\Models\Order;


class OrderController extends Controller
{

    public function __construct(
        protected OrderService $orderService
    ) {}



    public function store(StoreOrderRequest $request)
    {

        $order =
            $this->orderService
            ->createDraft(
                $request->validated()
            );


        return new OrderResource(
            $order->load('orderTable')
        );
    }

    public function show(Order $order): OrderResource
    {
        $order->load([
            'batches.items.menuVariant',
        ]);

        return new OrderResource($order);
    }
}
