<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderBatchStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderBatchResource;
use App\Http\Resources\OrderItemResource;
use App\Models\Order;
use App\Models\OrderBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KitchenController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id;

        $status = $request->query('status');

        $query = OrderBatch::query()
            ->when($outletId, function ($q) use ($outletId) {
                $q->whereHas('order', fn ($sub) => $sub->where('outlet_id', $outletId));
            })
            // Exclude orders that are still Draft or Cancelled (must be submitted to kitchen)
            ->whereHas('order', function ($sub) {
                $sub->whereNotIn('status', [OrderStatus::Draft, OrderStatus::Cancelled]);
            })
            // Exclude batches that are Draft/Waiting (not yet sent to kitchen), Completed, or Cancelled
            ->whereNotIn('status', [
                OrderBatchStatus::Waiting,
                OrderBatchStatus::Completed,
                OrderBatchStatus::Cancelled,
            ])
            // Exclude batches whose items are all served or cancelled
            ->where(function ($q) {
                $q->whereDoesntHave('items')
                    ->orWhereHas('items', function ($sub) {
                        $sub->whereNotIn('status', [
                            \App\Enums\OrderItemStatus::Served,
                            \App\Enums\OrderItemStatus::Cancelled,
                        ]);
                    });
            });

        if ($status && $status !== 'all') {
            $mappedStatus = match ($status) {
                'waiting', 'submitted', 'sent' => [OrderBatchStatus::Sent],
                'cooking', 'preparing' => [OrderBatchStatus::Cooking],
                'ready' => [OrderBatchStatus::Ready],
                default => [$status],
            };
            $query->whereIn('status', $mappedStatus);
        } else {
            // Default: only active operational batches that were sent to kitchen (Sent, Cooking, Ready)
            $query->whereIn('status', [
                OrderBatchStatus::Sent,
                OrderBatchStatus::Cooking,
                OrderBatchStatus::Ready,
            ]);
        }

        $batches = $query
            ->with([
                'order.orderTable',
                'items.changes',
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        return OrderBatchResource::collection($batches);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id;

        // Try finding by OrderBatch ID first (only non-draft, submitted batches)
        $batch = null;
        if (is_numeric($id)) {
            $batch = OrderBatch::query()
                ->when($outletId, function ($q) use ($outletId) {
                    $q->whereHas('order', fn ($sub) => $sub->where('outlet_id', $outletId));
                })
                ->whereHas('order', function ($sub) {
                    $sub->whereNotIn('status', [OrderStatus::Draft, OrderStatus::Cancelled]);
                })
                ->whereNotIn('status', [OrderBatchStatus::Waiting, OrderBatchStatus::Cancelled])
                ->with([
                    'order.orderTable',
                    'items.menuVariant.cookingMethod',
                    'items.menuVariant.kitchenStation',
                    'items.changes',
                ])
                ->find($id);

            if ($batch) {
                $order = $batch->order;
            }
        }

        // If not found by batch, find by Order ID or Order Number (must not be draft)
        if (! $batch) {
            $order = Order::query()
                ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
                ->whereNotIn('status', [OrderStatus::Draft, OrderStatus::Cancelled])
                ->where(function ($q) use ($id) {
                    if (is_numeric($id)) {
                        $q->where('id', $id);
                    } else {
                        $q->where('order_number', $id);
                    }
                })
                ->with([
                    'orderTable',
                    'batches.items.menuVariant.cookingMethod',
                    'batches.items.menuVariant.kitchenStation',
                ])
                ->first();
        }

        if (! $order && ! $batch) {
            return response()->json([
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        // Collect items from the batch or all batches of the order
        if ($batch) {
            $items = $batch->items;
            $status = $batch->status->value ?? (string) $batch->status;
            $batchNumber = $batch->batch_number;
            $sentAt = $batch->sent_to_kitchen_at ?? $batch->created_at;
        } else {
            $items = $order->batches->flatMap(fn ($b) => $b->items);
            $status = $order->status->value ?? (string) $order->status;
            $batchNumber = $order->batches->first()?->batch_number ?? 1;
            $sentAt = $order->batches->first()?->sent_to_kitchen_at ?? $order->created_at;
        }

        return response()->json([
            'data' => [
                'id' => $batch ? $batch->id : $order->id,
                'order_id' => $order->id,
                'batch_number' => $batchNumber,
                'order_number' => $order->order_number,
                'table' => $order->orderTable ? ['number' => $order->orderTable->number] : null,
                'table_number' => $order->orderTable?->number,
                'customer_name' => $order->customer_name,
                'guest_count' => $order->guest_count,
                'status' => $status,
                'items' => OrderItemResource::collection($items),
                'note' => $order->notes ?? $order->note ?? null,
                'notes' => $order->notes ?? $order->note ?? null,
                'created_at' => $order->created_at,
                'sent_to_kitchen_at' => $sentAt,
            ],
        ]);
    }
}
