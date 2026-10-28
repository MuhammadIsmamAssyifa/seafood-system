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
            'menu_name' => $this->menu_name_snapshot,
            'variant_name' => $this->variant_name_snapshot,
            'quantity' => $this->quantity,
            'unit' => $this->sales_unit,
            'price' => $this->price_snapshot,
            'subtotal' => $this->subtotal,
            'status' => $this->status,
        ];
    }
}
