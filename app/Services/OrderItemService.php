<?php

namespace App\Services;

use App\Enums\OrderBatchStatus;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\SalesUnit;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\OrderBatch;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderItemService
{
    public function __construct(
        protected OrderCalculationService $calculationService
    ) {}

    private function getOrCreateWaitingBatch(Order $order): OrderBatch
    {
        /** @var OrderBatch|null $batch */
        $batch = $order->batches()
            ->where('status', OrderBatchStatus::Waiting)
            ->latest('batch_number')
            ->first();

        if ($batch) {
            return $batch;
        }

        $lastBatchNumber = (int) ($order->batches()->max('batch_number') ?? 0);

        /** @var OrderBatch $newBatch */
        $newBatch = $order->batches()->create([
            'batch_number' => $lastBatchNumber + 1,
            'status' => OrderBatchStatus::Waiting,
        ]);

        return $newBatch;
    }

    public function addItem(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {

            /*
        |--------------------------------------------------------------------------
        | Ambil batch aktif
        |--------------------------------------------------------------------------
        */

            if ($order->status !== OrderStatus::Draft) {
                throw new \DomainException(
                    'Order yang sudah dikirim ke dapur tidak dapat diubah.'
                );
            }

            $batch = $this->getOrCreateWaitingBatch($order);

            /*
        |--------------------------------------------------------------------------
        | Ambil menu variant
        |--------------------------------------------------------------------------
        */

            $menuVariant = MenuVariant::with('menu')
                ->where('status', 'active')
                ->findOrFail($data['menu_variant_id']);

            if (
                $menuVariant->sales_unit === SalesUnit::Portion->value ||
                $menuVariant->sales_unit === SalesUnit::Pcs->value
            ) {

                if ((int) $data['quantity'] != $data['quantity']) {
                    throw new \Exception(
                        'Quantity untuk portion atau pcs harus berupa angka bulat.'
                    );
                }
            }

            /*
        |--------------------------------------------------------------------------
        | Hitung subtotal
        |--------------------------------------------------------------------------
        */

            $subtotal = $menuVariant->price * $data['quantity'];

            /*
        |--------------------------------------------------------------------------
        | Simpan order item
        |--------------------------------------------------------------------------
        */

            $item = OrderItem::create([
                'order_batch_id' => $batch->id,
                'menu_variant_id' => $menuVariant->id,
                'menu_name_snapshot' => $menuVariant->menu->name,
                'variant_name_snapshot' => $menuVariant->name,
                'price_snapshot' => $menuVariant->price,
                'sales_unit' => $menuVariant->sales_unit,
                'quantity' => $data['quantity'],
                'subtotal' => $subtotal,
                'inventory_quantity' => $data['quantity'] * $menuVariant->conversion_rate,
                'status' => OrderItemStatus::Waiting,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Update total order
        |--------------------------------------------------------------------------
        */

            $this->calculationService->recalculate($order);

            return $order->fresh();
        });
    }

    public function updateItem(OrderItem $item, array $data): Order
    {
        return DB::transaction(function () use ($item, $data) {
            /** @var OrderBatch $batch */
            $batch = $item->orderBatch()->firstOrFail();
            /** @var Order $order */
            $order = $batch->order()->lockForUpdate()->firstOrFail();

            if ($order->status !== OrderStatus::Draft) {
                throw new \DomainException(
                    'Order yang sudah dikirim ke dapur tidak dapat diubah.'
                );
            }

            $quantity = $data['quantity'] ?? null;

            if (! is_numeric($quantity) || $quantity <= 0) {
                throw new \InvalidArgumentException('Quantity harus lebih dari 0.');
            }

            $quantity = (float) $quantity;

            $isIntegerUnit = in_array(
                $item->sales_unit instanceof SalesUnit
                    ? $item->sales_unit
                    : SalesUnit::from($item->sales_unit),
                [SalesUnit::Portion, SalesUnit::Pcs],
                true
            );

            if ($isIntegerUnit && floor($quantity) !== $quantity) {
                throw new \InvalidArgumentException(
                    'Quantity untuk portion atau pcs harus bilangan bulat.'
                );
            }

            if ($item->price_snapshot === null) {
                throw new \DomainException('Price snapshot tidak tersedia.');
            }

            $conversionRate = $item->menuVariant?->conversion_rate;
            if ($conversionRate === null) {
                throw new \DomainException('Conversion rate menu variant tidak tersedia.');
            }

            $item->update([
                'quantity' => $quantity,
                'subtotal' => $item->price_snapshot * $quantity,
                'inventory_quantity' => $quantity * $conversionRate,
            ]);

            $this->calculationService->recalculate($order);

            return $order->fresh();
        });
    }

    public function deleteItem(OrderItem $item): Order
    {
        return DB::transaction(function () use ($item) {
            /** @var OrderBatch $batch */
            $batch = $item->orderBatch()->firstOrFail();
            /** @var Order $order */
            $order = $batch->order()
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->status !== OrderStatus::Draft) {
                throw new \DomainException(
                    'Order yang sudah dikirim ke dapur tidak dapat diubah.'
                );
            }

            $item->delete();

            $this->calculationService->recalculate($order);

            return $order->fresh();
        });
    }
}
