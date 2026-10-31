<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Log an action in the system.
     */
    public function log(
        string $action,
        string $description,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null,
        ?int $outletId = null,
        ?string $module = null
    ): AuditLog {
        return $this->auditLogService->log(
            action: $action,
            description: $description,
            auditable: $auditable,
            oldValues: $oldValues,
            newValues: $newValues,
            userId: $userId,
            outletId: $outletId,
            module: $module
        );
    }

    /**
     * Retrieve audit history.
     */
    public function getHistory(
        int $outletId,
        ?string $action = null,
        ?int $userId = null,
        ?string $startDate = null,
        ?string $endDate = null,
        int $perPage = 15,
        ?string $module = null,
        ?string $search = null
    ): LengthAwarePaginator {
        return $this->auditLogService->getLogs(
            outletId: $outletId,
            action: $action,
            userId: $userId,
            startDate: $startDate,
            endDate: $endDate,
            perPage: $perPage,
            module: $module,
            search: $search
        );
    }
}

