<?php

namespace App\Services;

use App\Enums\OrderBatchStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
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
            if ($order->status === OrderStatus::Completed) {
                throw new \DomainException(
                    'Order sudah selesai.'
                );
            }

            $this->validateOrderCanBePaid($order);

            $paidAmount = $order->payments()
                ->where('status', PaymentStatus::Paid)
                ->sum('amount');

            $remaining = $order->grand_total - $paidAmount;

            if ($data['amount'] > $remaining) {
                throw new \DomainException(
                    'Jumlah pembayaran melebihi sisa tagihan.'
                );
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $data['method'],
                'status' => PaymentStatus::Paid,
                'amount' => $data['amount'],
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
        $paidAmount = $order->payments()
            ->where('status', PaymentStatus::Paid)
            ->sum('amount');

        if ($paidAmount >= $order->grand_total) {
            $order->update([
                'status' => OrderStatus::Completed,
            ]);
        }
    }

    private function validateOrderCanBePaid(Order $order): void
    {
        $unfinishedBatch = $order->batches()
            ->whereNotIn('status', [
                OrderBatchStatus::Ready,
                OrderBatchStatus::Completed,
            ])
            ->exists();

        if ($unfinishedBatch) {
            throw new \DomainException(
                'Masih ada pesanan yang belum selesai dari dapur.'
            );
        }
    }
}
