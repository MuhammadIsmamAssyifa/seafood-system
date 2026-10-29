<?php

namespace App\Events;

use App\Events\Concerns\ResolvesKdsChannel;
use App\Http\Resources\OrderBatchResource;
use App\Models\OrderBatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KdsBatchSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, ResolvesKdsChannel, SerializesModels;

    public function __construct(
        public OrderBatch $batch
    ) {
        $this->batch->loadMissing(['order.orderTable', 'items']);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel(static::channelName()),
        ];
    }

    public function broadcastAs(): string
    {
        return 'kds.batch.submitted';
    }

    public function broadcastWith(): array
    {
        return (new OrderBatchResource($this->batch))->resolve();
    }
}

