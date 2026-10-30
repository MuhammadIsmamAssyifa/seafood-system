<?php

namespace App\Services;

use App\Enums\OrderBatchStatus;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TableStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function createPayment(
        Order $order,
        array $data
    ): Payment {
        return DB::transaction(function () use ($order, $data) {
            if ($order->status === OrderStatus::Cancelled) {
                throw new \DomainException(
                    'Order telah dibatalkan.'
                );
            }

            $paidAmount = (float) $order->payments()
                ->where('status', PaymentStatus::Paid)
                ->sum('amount');

            $remaining = (float) $order->grand_total - $paidAmount;

            if ($remaining <= 0) {
                throw new \DomainException(
                    'Order sudah selesai dan lunas.'
                );
            }

            if ($data['amount'] > $remaining) {
                throw new \DomainException(
                    'Jumlah pembayaran melebihi sisa tagihan.'
                );
            }

            $cashReceived = isset($data['cash_received']) && $data['cash_received'] > 0
                ? (float) $data['cash_received']
                : null;
            $changeAmount = isset($data['change_amount']) && $data['change_amount'] > 0
                ? (float) $data['change_amount']
                : null;

            // Automatically compute change amount for cash payment if not supplied
            $methodVal = $data['method'] instanceof PaymentMethod ? $data['method']->value : (string) $data['method'];
            if ($methodVal === PaymentMethod::Cash->value && $cashReceived !== null && $cashReceived > $data['amount']) {
                $changeAmount ??= round($cashReceived - (float) $data['amount'], 2);
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $data['method'],
                'status' => PaymentStatus::Paid,
                'amount' => $data['amount'],
                'cash_received' => $cashReceived,
                'change_amount' => $changeAmount,
                'created_by' => auth()->id(),
                'paid_at' => now(),
            ]);

            $this->checkOrderPaid($order);

            $this->auditLogService->logPayment($payment);

            return $payment;
        });
    }

    public function getSummary(Order $order): array
    {
        $paid = (float) $order->payments()
            ->where('status', PaymentStatus::Paid)
            ->sum('amount');

        $grandTotal = (float) $order->grand_total;

        return [
            'order_id' => $order->id,
            'grand_total' => $grandTotal,
            'paid' => $paid,
            'remaining' => max(0, round($grandTotal - $paid, 2)),
        ];
    }

    private function checkOrderPaid(Order $order): void
    {
        $paidAmount = (float) $order->payments()
            ->where('status', PaymentStatus::Paid)
            ->sum('amount');

        if ($paidAmount >= (float) $order->grand_total) {
            $items = $order->items;
            $allServed = $items->isNotEmpty() && $items->every(
                fn ($item) => $item->status === OrderItemStatus::Served
            );

            // If all items are served or order has no kitchen items, mark completed and free table
            if ($allServed || $items->isEmpty()) {
                $order->update([
                    'status' => OrderStatus::Completed,
                ]);

                $order->orderTable?->update([
                    'status' => TableStatus::Available,
                ]);
            }
        }
    }
}
