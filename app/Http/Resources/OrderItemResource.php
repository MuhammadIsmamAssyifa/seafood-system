<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'order_batch_id' => $this->order_batch_id,
            'menu_variant_id' => $this->menu_variant_id,
            'menu_name' => $this->menu_name_snapshot,
            'variant_name' => $this->variant_name_snapshot,
            'quantity' => (float) $this->quantity,
            'requested_quantity' => $this->requested_quantity !== null ? (float) $this->requested_quantity : null,
            'confirmed_quantity' => $this->confirmed_quantity !== null ? (float) $this->confirmed_quantity : null,
            'unit' => $this->sales_unit,
            'price' => (float) $this->price_snapshot,
            'subtotal' => (float) $this->subtotal,
            'cogs' => (float) $this->cogs,
            'gross_profit' => (float) $this->gross_profit,
            'status' => $this->status,
        ];
    }
}
