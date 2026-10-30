<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'method' => $this->method,
            'status' => $this->status,
            'amount' => (float) $this->amount,
            'cash_received' => $this->cash_received !== null ? (float) $this->cash_received : null,
            'change_amount' => $this->change_amount !== null ? (float) $this->change_amount : null,
            'created_by' => $this->created_by,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
        ];
    }
}
