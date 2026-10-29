<?php

namespace App\Services;

use App\Enums\OrderBatchStatus;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\Order;
use App\Models\OrderBatch;
use App\Models\OrderTable;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function createDraft(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $table = OrderTable::lockForUpdate()->findOrFail($data['order_table_id']);

            if ($table->status === TableStatus::Occupied) {
                throw new \DomainException('Meja sedang digunakan.');
            }

            $order = Order::create([
                'outlet_id' => $table->outlet_id,
                'order_table_id' => $table->id,
                'order_number' => 'ORD-'.date('YmdHis').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
                'customer_name' => $data['customer_name'] ?? null,
                'guest_count' => $data['guest_count'],
                'status' => OrderStatus::Draft,
                'subtotal' => 0,
                'discount' => 0,
                'grand_total' => 0,
                'total_cogs' => 0,
                'gross_profit' => 0,
                'created_by' => auth()->id(),
            ]);

            OrderBatch::create([
                'order_id' => $order->id,
                'batch_number' => 1,
                'status' => OrderBatchStatus::Waiting,
            ]);

            $table->update([
                'status' => TableStatus::Occupied,
            ]);

            $this->auditLogService->logCreateOrder($order);

            return $order->fresh();
        });
    }
}
