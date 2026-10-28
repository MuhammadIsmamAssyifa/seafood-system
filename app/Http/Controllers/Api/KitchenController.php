<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderBatchResource;
use App\Models\OrderBatch;
use App\Enums\OrderBatchStatus;

class KitchenController extends Controller
{
    public function index()
    {
        $batches = OrderBatch::query()
            ->whereIn('status', [
                OrderBatchStatus::Sent,
                OrderBatchStatus::Cooking,
            ])
            ->with([
                'order',
                'items',
            ])
            ->orderBy('created_at')
            ->get();

        return OrderBatchResource::collection($batches);
    }
}
