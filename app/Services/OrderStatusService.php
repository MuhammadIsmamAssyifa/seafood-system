<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\TableStatus;
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

            $isPaid = (float) $order->payments()
                ->where('status', PaymentStatus::Paid)
                ->sum('amount') >= (float) $order->grand_total && $order->grand_total > 0;

            $newStatus = match (true) {
                $items->every(
                    fn ($item) => $item->status === OrderItemStatus::Served
                ) => $isPaid ? OrderStatus::Completed : OrderStatus::Ready,

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

                if ($newStatus === OrderStatus::Completed) {
                    $order->orderTable?->update([
                        'status' => TableStatus::Available,
                    ]);
                }

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
