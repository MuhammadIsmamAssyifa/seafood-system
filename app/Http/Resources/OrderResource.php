<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'outlet_id' => $this->outlet_id,
            'order_table_id' => $this->order_table_id,
            'order_number' => $this->order_number,
            'customer_name' => $this->customer_name,
            'guest_count' => $this->guest_count,
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'grand_total' => (float) $this->grand_total,
            'total_cogs' => (float) $this->total_cogs,
            'gross_profit' => (float) $this->gross_profit,
            'table' => $this->whenLoaded('orderTable', fn () => [
                'id' => $this->orderTable->id,
                'number' => $this->orderTable->number,
            ], [
                'id' => $this->orderTable?->id,
                'number' => $this->orderTable?->number,
            ]),
            'batches' => OrderBatchResource::collection($this->whenLoaded('batches')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at,
        ];
    }
}
