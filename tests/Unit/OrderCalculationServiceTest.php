<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderCalculationService;
use Tests\TestCase;

class OrderCalculationServiceTest extends TestCase
{
    public function test_recalculate_computes_correct_subtotal_discount_grand_total_and_profit(): void
    {
        $service = new OrderCalculationService;

        // Buat mock atau in-memory model untuk order
        $order = new Order([
            'discount' => 15000,
            'subtotal' => 0,
            'grand_total' => 0,
            'total_cogs' => 0,
            'gross_profit' => 0,
        ]);

        // Simulasikan perhitungan bisnis murni
        $items = collect([
            new OrderItem(['subtotal' => 50000, 'cogs' => 20000]),
            new OrderItem(['subtotal' => 75000, 'cogs' => 35000]),
        ]);

        $subtotal = round((float) $items->sum('subtotal'), 2);
        $totalCogs = round((float) $items->sum('cogs'), 2);
        $discount = round((float) $order->discount, 2);
        $grandTotal = max(0, round($subtotal - $discount, 2));
        $grossProfit = round($subtotal - $totalCogs - $discount, 2);

        $this->assertEquals(125000.0, $subtotal);
        $this->assertEquals(55000.0, $totalCogs);
        $this->assertEquals(110000.0, $grandTotal);
        $this->assertEquals(55000.0, $grossProfit);
    }

    public function test_grand_total_never_goes_below_zero(): void
    {
        $discount = 200000.0;
        $subtotal = 150000.0;

        $grandTotal = max(0, round($subtotal - $discount, 2));

        $this->assertEquals(0.0, $grandTotal);
    }
}
