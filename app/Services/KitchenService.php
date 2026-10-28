<?php

namespace App\Services;

use App\Enums\OrderBatchStatus;
use App\Enums\OrderItemStatus;
use App\Models\OrderBatch;
use App\Models\OrderItem;
use App\Services\InventoryService;
use App\Services\OrderStatusService;
use Illuminate\Support\Facades\DB;

class KitchenService
{

    public function __construct(
        protected InventoryService $inventoryService,
        protected OrderStatusService $orderStatusService
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


            return $batch->fresh();
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


            $ingredient = $item->menuVariant
                ->mainIngredient;

            if ($ingredient) {

                $this->inventoryService
                    ->deductStock(
                        $ingredient,
                        $item->quantity,
                        $item
                    );
            }

            $item->update([
                'status' => OrderItemStatus::Preparing,
            ]);


            $this->orderStatusService
                ->sync($item->order);


            $this->syncBatchStatus(
                $item->orderBatch
            );


            return $item->fresh();
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

            $this->orderStatusService
                ->sync($item->order);


            $this->syncBatchStatus(
                $item->orderBatch
            );


            return $item->fresh();
        });
    }

    private function syncBatchStatus(OrderBatch $batch): void
    {
        $items = $batch->items;


        if (
            $items->every(
                fn($item) =>
                in_array(
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
                fn($item) =>
                $item->status === OrderItemStatus::Preparing
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


            $this->orderStatusService
                ->sync($item->order);


            $this->syncBatchStatus(
                $item->orderBatch
            );


            return $item->fresh();
        });
    }
}
