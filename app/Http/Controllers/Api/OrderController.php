<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Get paginated orders list with eager loading.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id;

        $orders = Order::query()
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->with([
                'orderTable',
                'batches.items',
            ])
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        $order = $this->orderService->createDraft(
            $request->validated()
        );

        return (new OrderResource($order->load('orderTable')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Order $order): OrderResource
    {
        $order->load([
            'batches.items.menuVariant',
            'orderTable',
        ]);

        return new OrderResource($order);
    }
}
