<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderBatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $hasOrder = $this->relationLoaded('order');
        $order = $hasOrder ? $this->order : null;
        $hasTable = $order && $order->relationLoaded('orderTable');
        $tableNumber = $hasTable ? $order->orderTable?->number : null;

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'batch_number' => $this->batch_number,
            'status' => $this->status,
            'sent_to_kitchen_at' => $this->sent_to_kitchen_at,
            'order_number' => $order?->order_number,
            'customer_name' => $order?->customer_name,
            'guest_count' => $order?->guest_count,
            'table_number' => $tableNumber,
            'table' => $tableNumber !== null ? ['number' => $tableNumber] : null,
            'order' => $this->whenLoaded('order', fn () => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'table_number' => $this->order->relationLoaded('orderTable') ? $this->order->orderTable?->number : null,
                'customer_name' => $this->order->customer_name,
                'guest_count' => $this->order->guest_count,
                'status' => $this->order->status,
            ]),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
        ];
    }
}
