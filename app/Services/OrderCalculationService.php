<?php

namespace App\Services;

use App\Models\Order;

class OrderCalculationService
{
    public function recalculate(Order $order): Order
    {
        $items = $order->batches()
            ->with('items')
            ->get()
            ->flatMap(fn ($batch) => $batch->items);

        $subtotal = round((float) $items->sum('subtotal'), 2);
        $totalCogs = round((float) $items->sum('cogs'), 2);
        $discount = round((float) $order->discount, 2);
        $grandTotal = max(0, round($subtotal - $discount, 2));
        $grossProfit = round($subtotal - $totalCogs - $discount, 2);

        $order->update([
            'subtotal' => $subtotal,
            'total_cogs' => $totalCogs,
            'gross_profit' => $grossProfit,
            'grand_total' => $grandTotal,
        ]);

        return $order->fresh();
    }
}
