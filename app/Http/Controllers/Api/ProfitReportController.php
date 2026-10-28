<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderProfitResource;
use App\Http\Resources\ProfitSummaryResource;
use App\Models\Order;
use App\Services\ProfitCalculationService;
use Illuminate\Http\Request;

class ProfitReportController extends Controller
{
    public function __construct(
        protected ProfitCalculationService $profitService
    ) {}

    public function summary(Request $request): ProfitSummaryResource
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id ?? 1;

        $summary = $this->profitService->getProfitSummary(
            $outletId,
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        return new ProfitSummaryResource($summary);
    }

    public function orderProfit(Order $order): OrderProfitResource
    {
        $profit = $this->profitService->getOrderProfit($order);

        return new OrderProfitResource($profit);
    }
}

