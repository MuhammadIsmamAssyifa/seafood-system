<?php

namespace App\Events;

use App\Events\Concerns\ResolvesKdsChannel;
use App\Models\OrderItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KdsItemUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, ResolvesKdsChannel, SerializesModels;

    public function __construct(
        public OrderItem $item
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel(static::channelName()),
        ];
    }

    public function broadcastAs(): string
    {
        return 'kds.item.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->item->id,
            'order_batch_id' => $this->item->order_batch_id,
            'status' => $this->item->status instanceof \BackedEnum ? $this->item->status->value : $this->item->status,
            'menu_name' => $this->item->menu_name_snapshot,
            'variant_name' => $this->item->variant_name_snapshot,
            'quantity' => (float) $this->item->quantity,
            'updated_at' => $this->item->updated_at?->toISOString(),
        ];
    }
}
