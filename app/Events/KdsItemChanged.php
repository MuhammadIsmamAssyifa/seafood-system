<?php

namespace App\Events;

use App\Events\Concerns\ResolvesKdsChannel;
use App\Models\OrderItemChange;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KdsItemChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, ResolvesKdsChannel, SerializesModels;

    public function __construct(
        public OrderItemChange $change
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel(static::channelName()),
        ];
    }

    public function broadcastAs(): string
    {
        return 'kds.item.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->change->id,
            'order_item_id' => $this->change->order_item_id,
            'type' => $this->change->type instanceof \BackedEnum ? $this->change->type->value : $this->change->type,
            'old_quantity' => (float) $this->change->old_quantity,
            'new_quantity' => (float) $this->change->new_quantity,
            'old_price' => (float) $this->change->old_price,
            'new_price' => (float) $this->change->new_price,
            'status' => $this->change->status instanceof \BackedEnum ? $this->change->status->value : $this->change->status,
            'reason' => $this->change->reason,
            'updated_at' => $this->change->updated_at?->toISOString(),
        ];
    }
}
