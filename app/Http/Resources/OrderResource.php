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
            'order_number' => $this->order_number,
            'customer_name' => $this->customer_name,
            'guest_count' => $this->guest_count,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'grand_total' => $this->grand_total,
            'batches' => OrderBatchResource::collection($this->whenLoaded('batches')),
            'table' => ['id' => $this->orderTable?->id, 'number' => $this->orderTable?->number,],
            'created_at' => $this->created_at,
        ];
    }
}
