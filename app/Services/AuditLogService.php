<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItemChange;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class AuditLogService
{
    /**
     * Create an audit log record.
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
        $user = auth()->user();
        $effectiveUserId = $userId ?? $user?->id;
        $effectiveOutletId = $outletId ?? $user?->outlets()->first()?->id;

        return AuditLog::create([
            'outlet_id' => $effectiveOutletId,
            'user_id' => $effectiveUserId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log user login
     */
    public function logLogin(User $user, string $type = 'web'): AuditLog
    {
        $identifier = $user->email ?? $user->username ?? 'Unknown';

        return $this->log(
            action: 'login',
            description: "Pengguna {$user->name} ({$identifier}) berhasil login via {$type}.",
            auditable: $user,
            userId: $user->id,
            outletId: $user->outlets()->first()?->id
        );
    }

    /**
     * Log order creation
     */
    public function logCreateOrder(Order $order): AuditLog
    {
        $tableNum = $order->orderTable?->number ?? '-';

        return $this->log(
            action: 'create_order',
            description: "Membuat pesanan baru #{$order->order_number} di Meja {$tableNum} (Tamu: {$order->guest_count}, Pelanggan: {$order->customer_name}).",
            auditable: $order,
            newValues: [
                'order_number' => $order->order_number,
                'table_number' => $tableNum,
                'guest_count' => $order->guest_count,
                'customer_name' => $order->customer_name,
            ],
            userId: $order->created_by,
            outletId: $order->outlet_id
        );
    }

    /**
     * Log order cancellation
     */
    public function logCancelOrder(Order $order, string $reason): AuditLog
    {
        return $this->log(
            action: 'cancel_order',
            description: "Membatalkan pesanan #{$order->order_number}. Alasan: {$reason}",
            auditable: $order,
            oldValues: ['status' => $order->getOriginal('status') ?? 'active'],
            newValues: ['status' => 'cancelled', 'reason' => $reason],
            userId: auth()->id() ?? $order->created_by,
            outletId: $order->outlet_id
        );
    }

    /**
     * Log payment transaction
     */
    public function logPayment(Payment $payment): AuditLog
    {
        $order = $payment->order;
        $method = $payment->method instanceof \BackedEnum ? $payment->method->value : $payment->method;
        $amountFormatted = number_format((float) $payment->amount, 0, ',', '.');

        return $this->log(
            action: 'payment',
            description: "Menerima pembayaran {$method} sebesar Rp {$amountFormatted} untuk pesanan #{$order?->order_number}.",
            auditable: $payment,
            newValues: [
                'payment_id' => $payment->id,
                'order_number' => $order?->order_number,
                'method' => $method,
                'amount' => (float) $payment->amount,
            ],
            userId: $payment->created_by,
            outletId: $order?->outlet_id
        );
    }

    /**
     * Log inventory adjustment / stock movement
     */
    public function logInventoryAdjustment(StockMovement $movement, string $note = ''): AuditLog
    {
        $ing = $movement->ingredient;
        $unit = $ing?->unit instanceof \BackedEnum ? $ing->unit->value : $ing?->unit;
        $type = $movement->type instanceof \BackedEnum ? $movement->type->value : $movement->type;

        return $this->log(
            action: 'inventory_adjustment',
            description: "Mutasi inventori ({$type}) bahan baku {$ing?->name} sebanyak {$movement->quantity} {$unit}. Catatan: {$note}",
            auditable: $movement,
            newValues: [
                'ingredient' => $ing?->name,
                'type' => $type,
                'quantity' => (float) $movement->quantity,
                'cost_per_unit' => (float) $movement->cost_per_unit,
                'total_cost' => (float) $movement->total_cost,
            ],
            userId: $movement->created_by,
            outletId: $movement->outlet_id
        );
    }

    /**
     * Log price & quantity changes (Dynamic Price)
     */
    public function logPriceChange(OrderItemChange $change): AuditLog
    {
        $item = $change->orderItem;
        $order = $item?->orderBatch?->order ?? $item?->order;

        return $this->log(
            action: 'price_change',
            description: "Perubahan item {$item?->menu_name_snapshot} pada pesanan #{$order?->order_number}: Qty ({$change->old_quantity} -> {$change->new_quantity}), Harga (Rp ".number_format((float) $change->old_price, 0).' -> Rp '.number_format((float) $change->new_price, 0)."). Alasan: {$change->reason}",
            auditable: $change,
            oldValues: [
                'quantity' => (float) $change->old_quantity,
                'price' => (float) $change->old_price,
            ],
            newValues: [
                'quantity' => (float) $change->new_quantity,
                'price' => (float) $change->new_price,
                'status' => $change->status instanceof \BackedEnum ? $change->status->value : $change->status,
                'reason' => $change->reason,
            ],
            userId: auth()->id() ?? $change->created_by,
            outletId: $order?->outlet_id
        );
    }

    /**
     * Log menu deletion
     */
    public function logDeleteMenu(Menu $menu): AuditLog
    {
        return $this->log(
            action: 'delete_menu',
            description: "Menghapus menu: {$menu->name} (ID: {$menu->id}).",
            auditable: $menu,
            oldValues: [
                'id' => $menu->id,
                'name' => $menu->name,
                'type' => $menu->type instanceof \BackedEnum ? $menu->type->value : $menu->type,
            ],
            userId: auth()->id(),
            outletId: $menu->outlet_id
        );
    }

    /**
     * Log user management action
     */
    public function logUserManagement(
        string $actionDesc,
        User $targetUser,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        $identifier = $targetUser->email ?? $targetUser->username ?? 'Unknown';

        return $this->log(
            action: 'user_management',
            description: "Manajemen user: {$actionDesc} untuk pengguna {$targetUser->name} ({$identifier}).",
            auditable: $targetUser,
            oldValues: $oldValues,
            newValues: $newValues,
            userId: auth()->id()
        );
    }

    /**
     * Query and filter audit logs
     */
    public function getLogs(
        int $outletId,
        ?string $action = null,
        ?int $userId = null,
        ?string $startDate = null,
        ?string $endDate = null,
        int $perPage = 15,
        ?string $module = null,
        ?string $search = null
    ): LengthAwarePaginator {
        $query = AuditLog::query()
            ->where(function ($q) use ($outletId) {
                $q->where('outlet_id', $outletId)
                    ->orWhereNull('outlet_id');
            })
            ->with(['user', 'outlet'])
            ->latest();

        if ($action) {
            $query->where('action', $action);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($module) {
            $moduleLower = strtolower($module);
            $query->where(function ($q) use ($module, $moduleLower) {
                $q->where('module', 'ILIKE', "%{$module}%")
                    ->orWhere(function ($sub) use ($moduleLower) {
                        if ($moduleLower === 'order') {
                            $sub->where('action', 'LIKE', '%order%');
                        } elseif ($moduleLower === 'inventory') {
                            $sub->where(function ($i) {
                                $i->where('action', 'LIKE', '%stock%')
                                    ->orWhere('action', 'LIKE', '%inventory%')
                                    ->orWhere('action', 'LIKE', '%waste%');
                            });
                        } elseif ($moduleLower === 'menu') {
                            $sub->where('action', 'LIKE', '%menu%');
                        } elseif ($moduleLower === 'employee' || $moduleLower === 'user') {
                            $sub->where(function ($u) {
                                $u->where('action', 'LIKE', '%user%')
                                    ->orWhere('action', 'LIKE', '%role%')
                                    ->orWhere('action', 'LIKE', '%login%')
                                    ->orWhere('action', 'LIKE', '%logout%');
                            });
                        } elseif ($moduleLower === 'settings' || $moduleLower === 'setting') {
                            $sub->where('action', 'LIKE', '%setting%');
                        } elseif ($moduleLower === 'payment') {
                            $sub->where('action', 'LIKE', '%payment%');
                        }
                    });
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'ILIKE', "%{$search}%")
                    ->orWhere('action', 'ILIKE', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('email', 'ILIKE', "%{$search}%")
                            ->orWhere('username', 'ILIKE', "%{$search}%");
                    });
            });
        }

        if ($startDate) {
            $query->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
        }

        if ($endDate) {
            $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        return $query->paginate($perPage);
    }

    /**
     * Get single log detail with relations.
     */
    public function getLogById(int $id): ?AuditLog
    {
        return AuditLog::with(['user', 'outlet', 'auditable'])->find($id);
    }

    /**
     * Get summary metrics for audit logs.
     */
    public function getSummary(int $outletId): array
    {
        $baseQuery = AuditLog::where(function ($q) use ($outletId) {
            $q->where('outlet_id', $outletId)
                ->orWhereNull('outlet_id');
        });

        $totalLogs = (clone $baseQuery)->count();
        $todayLogs = (clone $baseQuery)->whereDate('created_at', Carbon::today())->count();
        $uniqueUsers = (clone $baseQuery)->whereNotNull('user_id')->distinct('user_id')->count('user_id');

        $recentBreakdown = (clone $baseQuery)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('count', 'action')
            ->toArray();

        return [
            'total_logs' => $totalLogs,
            'today_logs' => $todayLogs,
            'unique_users' => $uniqueUsers,
            'top_actions' => $recentBreakdown,
        ];
    }
}
