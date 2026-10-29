<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderBatchStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderBatchResource;
use App\Models\OrderBatch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KitchenController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id;

        $batches = OrderBatch::query()
            ->when($outletId, function ($q) use ($outletId) {
                $q->whereHas('order', fn ($sub) => $sub->where('outlet_id', $outletId));
            })
            ->whereIn('status', [
                OrderBatchStatus::Sent,
                OrderBatchStatus::Cooking,
            ])
            ->with([
                'order.orderTable',
                'items',
            ])
            ->orderBy('created_at')
            ->get();

        return OrderBatchResource::collection($batches);
    }
}
