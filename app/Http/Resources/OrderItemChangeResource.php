<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemChangeResource extends JsonResource
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
            'order_item_id' => $this->order_item_id,
            'type' => $this->type,
            'status' => $this->status,
            'reason' => $this->reason,
            'old_quantity' => (float) $this->old_quantity,
            'old_price' => (float) $this->old_price,
            'new_quantity' => $this->new_quantity !== null ? (float) $this->new_quantity : null,
            'new_price' => $this->new_price !== null ? (float) $this->new_price : null,
            'replacement_item_id' => $this->replacement_item_id,
            'replacement_item' => new OrderItemResource($this->whenLoaded('replacementItem')),
            'order_item' => new OrderItemResource($this->whenLoaded('orderItem')),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
        ];
    }
}
