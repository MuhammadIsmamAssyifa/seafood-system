<?php

namespace App\Events;

use App\Events\Concerns\ResolvesKdsChannel;
use App\Models\OrderBatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KdsBatchUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, ResolvesKdsChannel, SerializesModels;

    public function __construct(
        public OrderBatch $batch
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel(static::channelName()),
        ];
    }

    public function broadcastAs(): string
    {
        return 'kds.batch.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->batch->id,
            'order_id' => $this->batch->order_id,
            'status' => $this->batch->status instanceof \BackedEnum ? $this->batch->status->value : $this->batch->status,
            'updated_at' => $this->batch->updated_at?->toISOString(),
        ];
    }
}
