<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderTable;
use App\Models\OrderBatch;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\OrderItem;
use App\Models\MenuVariant;
use App\Enums\OrderItemStatus;
use App\Enums\OrderBatchStatus;
use App\Enums\SalesUnit;
use Illuminate\Support\Facades\DB;

class OrderService
{

    public function createDraft(array $data): Order
    {

        return DB::transaction(function () use ($data) {

            $table = OrderTable::lockForUpdate()
                ->findOrFail(
                    $data['order_table_id']
                );
            if (
                $table->status === TableStatus::Occupied
            ) {
                throw new \Exception(
                    'Table already occupied'
                );
            }

            $order = Order::create([
                'outlet_id' => $table->outlet_id,
                'order_table_id' => $table->id,
                'order_number' => 'ORD-' . time(),
                'customer_name' => $data['customer_name'] ?? null,
                'guest_count' => $data['guest_count'],
                'status' => OrderStatus::Draft,
                'subtotal' => 0,
                'discount' => 0,
                'grand_total' => 0,
                'created_by' => auth()->id()

            ]);

            OrderBatch::create([
                'order_id' => $order->id,
                'batch_number' => 1,
                'status' => OrderBatchStatus::Waiting,
            ]);

            $table->update([
                'status' => TableStatus::Occupied
            ]);

            return $order->fresh();
        });
    }
}
