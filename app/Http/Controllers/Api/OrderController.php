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
     * Get paginated orders list with eager loading, search, and date filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id;

        $orders = Order::query()
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('order_number', 'ILIKE', "%{$search}%")
                        ->orWhere('customer_name', 'ILIKE', "%{$search}%");
                });
            })
            ->when($request->query('date'), function ($q, $date) {
                $q->whereDate('created_at', $date);
            })
            ->when($request->query('start_date'), function ($q, $start) {
                $q->whereDate('created_at', '>=', $start);
            })
            ->when($request->query('end_date'), function ($q, $end) {
                $q->whereDate('created_at', '<=', $end);
            })
            ->with([
                'orderTable',
                'batches.items',
                'payments',
            ])
            ->latest()
            ->paginate($request->integer('per_page', 20));

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
            'batches.items.menuVariant.menu',
            'orderTable',
            'payments.createdBy',
            'statusHistories.changedBy',
            'createdBy',
        ]);

        return new OrderResource($order);
    }
}
