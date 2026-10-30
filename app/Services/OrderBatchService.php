<?php

namespace App\Services;

use App\Enums\OrderBatchStatus;
use App\Enums\OrderStatus;
use App\Events\KdsBatchSubmitted;
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
                'sent_to_kitchen_at' => now(),
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

            $freshBatch = $batch->fresh();
            DB::afterCommit(function () use ($freshBatch) {
                event(new KdsBatchSubmitted($freshBatch));
            });

            return $freshBatch;
        });
    }

    public function delete(OrderBatch $batch): void
    {
        DB::transaction(function () use ($batch) {
            if ($batch->status !== OrderBatchStatus::Waiting) {
                throw new \DomainException(
                    'Batch yang sudah dikirim ke dapur tidak dapat dihapus.'
                );
            }

            $order = $batch->order;

            $batch->items()->delete();
            $batch->delete();

            if ($order) {
                app(OrderCalculationService::class)->recalculate($order);
            }
        });
    }
}
