<?php

namespace App\Services;

use App\Enums\OrderBatchStatus;
use App\Enums\OrderStatus;
use App\Models\OrderBatch;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;

class OrderBatchService
{
    public function submit(OrderBatch $batch)
    {
        return DB::transaction(function () use ($batch) {

            $order = $batch->order;

            if ($batch->status !== OrderBatchStatus::Waiting) {
                throw new \DomainException(
                    'Batch ini sudah tidak dapat dikirim.'
                );
            }

            if ($batch->items()->count() === 0) {
                throw new \DomainException(
                    'Batch tidak memiliki item.'
                );
            }

            $batch->update([
                'status' => OrderBatchStatus::Sent,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Update Order Status
            |--------------------------------------------------------------------------
            */

            if ($order->status === OrderStatus::Draft) {

                $oldStatus = $order->status;

                $order->update([
                    'status' => OrderStatus::Submitted,
                ]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $oldStatus,
                    'status' => OrderStatus::Submitted,
                    'changed_by' => auth()->id(),
                ]);
            }

            return $batch->fresh();
        });
    }
}
