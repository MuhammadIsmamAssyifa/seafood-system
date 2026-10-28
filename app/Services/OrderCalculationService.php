<?php

namespace App\Services;

use App\Models\Order;

class OrderCalculationService
{
    public function recalculate(Order $order)
    {
        $subtotal = $order->batches()
            ->with('items')
            ->get()
            ->flatMap->items
            ->sum('subtotal');


        $order->update([
            'subtotal' => $subtotal,
            'grand_total' => $subtotal,
        ]);
    }
}
