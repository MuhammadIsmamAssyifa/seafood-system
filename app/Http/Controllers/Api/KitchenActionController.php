<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderBatch;
use App\Models\OrderItem;
use App\Services\KitchenService;

class KitchenActionController extends Controller
{

    public function __construct(
        protected KitchenService $kitchenService
    ) {}


    public function acceptBatch(OrderBatch $batch)
    {
        return response()->json(
            $this->kitchenService
                ->acceptBatch($batch)
        );
    }


    public function prepareItem(OrderItem $item)
    {
        return response()->json(
            $this->kitchenService
                ->prepareItem($item)
        );
    }


    public function readyItem(OrderItem $item)
    {
        return response()->json(
            $this->kitchenService
                ->readyItem($item)
        );
    }

    public function serveItem(OrderItem $item)
    {
        return response()->json(
            $this->kitchenService
                ->serveItem($item)
        );
    }
}
