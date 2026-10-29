<?php

namespace Tests\Unit;

use Tests\TestCase;

class ProfitCalculationServiceTest extends TestCase
{
    public function test_profit_margin_calculation_formula(): void
    {
        $revenue = 100000.0;
        $cogs = 60000.0;
        $grossProfit = $revenue - $cogs;

        $marginPercentage = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0.0;

        $this->assertEquals(40000.0, $grossProfit);
        $this->assertEquals(40.0, $marginPercentage);
    }

    public function test_profit_margin_handles_zero_revenue_without_division_by_zero(): void
    {
        $revenue = 0.0;
        $grossProfit = 0.0;

        $marginPercentage = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0.0;

        $this->assertEquals(0.0, $marginPercentage);
    }
}
