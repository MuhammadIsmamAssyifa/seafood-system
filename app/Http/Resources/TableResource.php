<?php

namespace App\Http\Resources;

use App\Enums\OrderStatus;
use App\Models\OrderTable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderTable
 */
class TableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var OrderTable $table */
        $table = $this->resource;

        $activeOrder = $table->orders()
            ->whereNotIn('status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value])
            ->latest('id')
            ->first();

        return [
            'id' => $table->id,
            'number' => $table->number,
            'status' => $activeOrder ? 'occupied' : 'available',
            'outlet_id' => $table->outlet_id,
            'active_order' => $activeOrder ? [
                'id' => $activeOrder->id,
                'order_number' => $activeOrder->order_number,
                'customer_name' => $activeOrder->customer_name,
                'guest_count' => $activeOrder->guest_count,
                'status' => $activeOrder->status->value ?? (string) $activeOrder->status,
                'subtotal' => (float) $activeOrder->subtotal,
                'grand_total' => (float) $activeOrder->grand_total,
                'created_at' => $activeOrder->created_at?->toIso8601String(),
            ] : null,
            'created_at' => $table->created_at?->toIso8601String(),
            'updated_at' => $table->updated_at?->toIso8601String(),
        ];
    }
}

