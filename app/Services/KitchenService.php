<?php

namespace App\Services;

use App\Enums\OrderBatchStatus;
use App\Enums\OrderItemStatus;
use App\Events\KdsBatchUpdated;
use App\Events\KdsItemUpdated;
use App\Models\Ingredient;
use App\Models\MenuVariant;
use App\Models\OrderBatch;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class KitchenService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected OrderStatusService $orderStatusService,
        protected OrderCalculationService $calculationService
    ) {}

    public function acceptBatch(OrderBatch $batch): OrderBatch
    {
        return DB::transaction(function () use ($batch) {
            if ($batch->status !== OrderBatchStatus::Sent) {
                throw new \DomainException(
                    'Batch belum dikirim ke dapur.'
                );
            }

            $batch->update([
                'status' => OrderBatchStatus::Cooking,
            ]);

            $freshBatch = $batch->fresh();
            event(new KdsBatchUpdated($freshBatch));

            return $freshBatch;
        });
    }

    public function prepareItem(OrderItem $item): OrderItem
    {
        return DB::transaction(function () use ($item) {
            if ($item->status !== OrderItemStatus::Waiting) {
                throw new \DomainException(
                    'Item tidak dapat diproses.'
                );
            }

            /** @var MenuVariant|null $variant */
            $variant = $item->menuVariant;
            $recipe = $variant?->recipe()->with('items.ingredient')->first();
            $itemCogs = 0;

            if ($recipe && $recipe->items->isNotEmpty()) {
                // Deduct based on recipe items
                foreach ($recipe->items as $recipeItem) {
                    $deductQty = (float) $recipeItem->quantity * (float) $item->quantity;
                    $deduction = $this->inventoryService->deductStock(
                        $recipeItem->ingredient,
                        $deductQty,
                        $item
                    );
                    $itemCogs += $deduction['cost'];
                }
            } elseif ($variant?->mainIngredient instanceof Ingredient) {
                // Deduct based on main ingredient and conversion rate
                $deductQty = (float) ($item->inventory_quantity > 0 ? $item->inventory_quantity : $item->quantity);
                $deduction = $this->inventoryService->deductStock(
                    $variant->mainIngredient,
                    $deductQty,
                    $item
                );
                $itemCogs += $deduction['cost'];
            }

            $grossProfit = round((float) $item->subtotal - $itemCogs, 2);

            $item->update([
                'status' => OrderItemStatus::Preparing,
                'cogs' => round($itemCogs, 2),
                'gross_profit' => $grossProfit,
            ]);

            $order = $item->orderBatch?->order ?? $item->order;
            if ($order) {
                $this->calculationService->recalculate($order);
                $this->orderStatusService->sync($order);
            }

            $batch = $item->orderBatch;
            if ($batch instanceof OrderBatch) {
                $this->syncBatchStatus($batch);
            }

            $freshItem = $item->fresh();
            event(new KdsItemUpdated($freshItem));

            return $freshItem;
        });
    }

    public function readyItem(OrderItem $item): OrderItem
    {
        return DB::transaction(function () use ($item) {
            if ($item->status !== OrderItemStatus::Preparing) {
                throw new \DomainException(
                    'Item belum dalam proses memasak.'
                );
            }

            $item->update([
                'status' => OrderItemStatus::Ready,
            ]);

            $order = $item->orderBatch?->order ?? $item->order;
            if ($order) {
                $this->orderStatusService->sync($order);
            }

            $batch = $item->orderBatch;
            if ($batch instanceof OrderBatch) {
                $this->syncBatchStatus($batch);
            }

            $freshItem = $item->fresh();
            event(new KdsItemUpdated($freshItem));

            return $freshItem;
        });
    }

    private function syncBatchStatus(OrderBatch $batch): void
    {
        $items = $batch->items;

        if (
            $items->every(
                fn ($item) => in_array(
                    $item->status,
                    [
                        OrderItemStatus::Ready,
                        OrderItemStatus::Served,
                    ]
                )
            )
        ) {
            $batch->update([
                'status' => OrderBatchStatus::Ready,
            ]);

            return;
        }

        if (
            $items->contains(
                fn ($item) => $item->status === OrderItemStatus::Preparing
            )
        ) {
            $batch->update([
                'status' => OrderBatchStatus::Cooking,
            ]);
        }
    }

    public function serveItem(OrderItem $item): OrderItem
    {
        return DB::transaction(function () use ($item) {
            if ($item->status !== OrderItemStatus::Ready) {
                throw new \DomainException(
                    'Item belum siap disajikan.'
                );
            }

            $item->update([
                'status' => OrderItemStatus::Served,
            ]);

            $order = $item->orderBatch?->order ?? $item->order;
            if ($order) {
                $this->orderStatusService->sync($order);
            }

            $batch = $item->orderBatch;
            if ($batch instanceof OrderBatch) {
                $this->syncBatchStatus($batch);
            }

            $freshItem = $item->fresh();
            event(new KdsItemUpdated($freshItem));

            return $freshItem;
        });
    }
}
