<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryReportResource;
use App\Http\Resources\MenuSalesReportResource;
use App\Http\Resources\PaymentReportResource;
use App\Http\Resources\SalesReportResource;
use App\Http\Resources\StockMovementReportResource;
use App\Services\ReportingService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportingService $reportingService
    ) {}

    public function sales(Request $request): SalesReportResource
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id ?? 1;

        $report = $this->reportingService->getSalesReport(
            $outletId,
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        return new SalesReportResource($report);
    }

    public function payments(Request $request): PaymentReportResource
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id ?? 1;

        $report = $this->reportingService->getPaymentReport(
            $outletId,
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        return new PaymentReportResource($report);
    }

    public function menuSales(Request $request): MenuSalesReportResource
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id ?? 1;

        $report = $this->reportingService->getMenuSalesReport(
            $outletId,
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        return new MenuSalesReportResource($report);
    }

    public function inventory(Request $request): InventoryReportResource
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id ?? 1;

        $report = $this->reportingService->getInventoryReport($outletId);

        return new InventoryReportResource($report);
    }

    public function stockMovements(Request $request): StockMovementReportResource
    {
        $validated = $request->validate([
            'ingredient_id' => ['nullable', 'integer', 'exists:ingredients,id'],
            'type' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id ?? 1;

        $report = $this->reportingService->getStockMovementReport(
            $outletId,
            $validated['ingredient_id'] ?? null,
            $validated['type'] ?? null,
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        return new StockMovementReportResource($report);
    }
}
