<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;

class OrderStatusService
{
    public function sync(Order $order, ?int $changedBy = null): Order
    {
        return DB::transaction(function () use ($order, $changedBy) {

            $items = $order->items()->get();

            if ($items->isEmpty()) {
                return $order;
            }

            $newStatus = match (true) {
                $items->every(
                    fn ($item) => $item->status === OrderItemStatus::Served
                ) => OrderStatus::Completed,

                $items->every(
                    fn ($item) => in_array(
                        $item->status,
                        [
                            OrderItemStatus::Ready,
                            OrderItemStatus::Served,
                        ]
                    )
                ) => OrderStatus::Ready,

                $items->contains(
                    fn ($item) => in_array(
                        $item->status,
                        [
                            OrderItemStatus::Preparing,
                            OrderItemStatus::Ready,
                        ]
                    )
                ) => OrderStatus::Preparing,

                default => $order->status,
            };

            if ($order->status !== $newStatus) {
                $oldStatus = $order->status;

                $order->update([
                    'status' => $newStatus,
                ]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $oldStatus,
                    'status' => $newStatus,
                    'changed_by' => $changedBy ?? auth()->id() ?? $order->created_by,
                ]);
            }

            return $order->fresh();
        });
    }
}
