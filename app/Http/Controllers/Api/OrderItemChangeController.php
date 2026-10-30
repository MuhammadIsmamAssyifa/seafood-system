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
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderItemChangeController extends Controller
{
    public function __construct(
        protected OrderItemChangeService $changeService
    ) {}

    /**
     * Daftar perubahan pesanan
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = OrderItemChange::with(['orderItem.orderBatch.order', 'replacementItem'])
            ->latest();

        if ($request->filled('order_id')) {
            $query->whereHas('orderItem.orderBatch', function ($q) use ($request) {
                $q->where('order_id', $request->order_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('order_item_id')) {
            $query->where('order_item_id', $request->order_item_id);
        }

        $changes = $query->paginate($request->integer('per_page', 50));

        return OrderItemChangeResource::collection($changes);
    }

    /**
     * Membuat request perubahan item
     */
    public function store(
        StoreOrderItemChangeRequest $request,
        ?OrderItem $orderItem = null
    ): OrderItemChangeResource {
        $item = $orderItem ?? OrderItem::findOrFail($request->validated('order_item_id'));

        $change = $this->changeService->create(
            $item,
            $request->validated()
        );

        return (new OrderItemChangeResource($change->load(['orderItem.orderBatch.order', 'replacementItem'])))
            ->additional(['message' => 'Perubahan order berhasil dibuat.']);
    }

    /**
     * Customer approve
     */
    public function approve(
        OrderItemChange $change
    ): OrderItemChangeResource {
        $result = $this->changeService->approve($change);

        return (new OrderItemChangeResource($result->load(['orderItem.orderBatch.order', 'replacementItem'])))
            ->additional(['message' => 'Perubahan order disetujui.']);
    }

    /**
     * Customer reject
     */
    public function reject(
        OrderItemChange $change
    ): OrderItemChangeResource {
        $result = $this->changeService->reject($change);

        return (new OrderItemChangeResource($result->load(['orderItem.orderBatch.order', 'replacementItem'])))
            ->additional(['message' => 'Perubahan order ditolak.']);
    }

    /**
     * Daftar perubahan pesanan untuk order tertentu
     */
    public function forOrder(\App\Models\Order $order): AnonymousResourceCollection
    {
        $changes = OrderItemChange::with(['orderItem.orderBatch.order', 'replacementItem'])
            ->whereHas('orderItem.orderBatch', function ($q) use ($order) {
                $q->where('order_id', $order->id);
            })
            ->latest()
            ->get();

        return OrderItemChangeResource::collection($changes);
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
