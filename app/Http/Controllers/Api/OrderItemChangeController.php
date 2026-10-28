<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderItemChange\ReplaceOrderItemRequest;
use App\Http\Requests\OrderItemChange\StoreOrderItemChangeRequest;
use App\Http\Resources\OrderItemChangeResource;
use App\Http\Resources\OrderItemResource;
use App\Models\OrderItem;
use App\Models\OrderItemChange;
use App\Services\OrderItemChangeService;

class OrderItemChangeController extends Controller
{
    public function __construct(
        protected OrderItemChangeService $changeService
    ) {}

    /**
     * Membuat request perubahan item
     */
    public function store(
        StoreOrderItemChangeRequest $request,
        OrderItem $orderItem
    ): OrderItemChangeResource {
        $change = $this->changeService->create(
            $orderItem,
            $request->validated()
        );

        return (new OrderItemChangeResource($change))
            ->additional(['message' => 'Perubahan order berhasil dibuat.']);
    }

    /**
     * Customer approve
     */
    public function approve(
        OrderItemChange $change
    ): OrderItemChangeResource {
        $result = $this->changeService->approve($change);

        return (new OrderItemChangeResource($result))
            ->additional(['message' => 'Perubahan order disetujui.']);
    }

    /**
     * Customer reject
     */
    public function reject(
        OrderItemChange $change
    ): OrderItemChangeResource {
        $result = $this->changeService->reject($change);

        return (new OrderItemChangeResource($result))
            ->additional(['message' => 'Perubahan order ditolak.']);
    }

    public function replace(
        ReplaceOrderItemRequest $request,
        OrderItem $orderItem
    ): OrderItemResource {
        $item = $this->changeService->replace(
            $orderItem,
            $request->validated()
        );

        return (new OrderItemResource($item))
            ->additional(['message' => 'Item berhasil diganti.']);
    }
}
