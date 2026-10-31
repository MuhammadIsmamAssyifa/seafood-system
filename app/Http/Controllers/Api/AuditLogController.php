<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Get paginated audit logs.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'action' => ['nullable', 'string', 'max:50'],
            'module' => ['nullable', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'outlet_id' => ['nullable', 'integer', 'exists:outlets,id'],
        ]);

        $outlet = $request->user()->outlets()->first();
        $outletId = $validated['outlet_id'] ?? $outlet?->id ?? 1;

        $logs = $this->auditLogService->getLogs(
            outletId: (int) $outletId,
            action: $validated['action'] ?? null,
            userId: isset($validated['user_id']) ? (int) $validated['user_id'] : null,
            startDate: $validated['start_date'] ?? null,
            endDate: $validated['end_date'] ?? null,
            perPage: (int) ($validated['per_page'] ?? 15),
            module: $validated['module'] ?? null,
            search: $validated['search'] ?? null,
        );

        return AuditLogResource::collection($logs);
    }

    /**
     * Get single audit log detail.
     */
    public function show(int $id): JsonResponse
    {
        $log = $this->auditLogService->getLogById($id);

        if (! $log) {
            return response()->json([
                'success' => false,
                'message' => 'Catatan audit log tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new AuditLogResource($log),
        ]);
    }

    /**
     * Get audit log metrics and summary.
     */
    public function summary(Request $request): JsonResponse
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $request->query('outlet_id') ?? $outlet?->id ?? 1;

        $summary = $this->auditLogService->getSummary((int) $outletId);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
