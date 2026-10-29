<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardSummaryResource;
use App\Services\ReportingService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportingService $reportingService
    ) {}

    public function summary(Request $request): DashboardSummaryResource
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id ?? 1;

        $summary = $this->reportingService->getDashboardSummary(
            $outletId,
            $validated['date'] ?? null
        );

        return new DashboardSummaryResource($summary);
    }
}
