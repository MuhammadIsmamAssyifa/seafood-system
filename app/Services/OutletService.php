<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OutletService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Get paginated or full outlets list.
     */
    public function getOutlets(
        ?int $restaurantId = null,
        ?string $status = null,
        ?string $search = null,
        bool $paginate = true,
        int $perPage = 15
    ): LengthAwarePaginator|Collection {
        $user = auth()->user();
        $effRestaurantId = $restaurantId ?? $user?->restaurant_id ?? Restaurant::first()?->id ?? 1;

        $query = Outlet::query()
            ->where('restaurant_id', $effRestaurantId)
            ->withCount(['users', 'menus', 'orderTables', 'ingredients'])
            ->with(['restaurant'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('code', 'ILIKE', "%{$search}%")
                    ->orWhere('address', 'ILIKE', "%{$search}%");
            });
        }

        if (! $paginate) {
            return $query->get();
        }

        return $query->paginate($perPage);
    }

    /**
     * Get single outlet detail with summary and assigned users.
     */
    public function getDetail(int $id): ?Outlet
    {
        $outlet = Outlet::with([
            'restaurant',
            'users' => fn ($q) => $q->with('roles')->select('users.id', 'users.name', 'users.email', 'users.status'),
        ])->withCount([
            'users',
            'menus',
            'orderTables',
            'ingredients',
            'orders as orders_today_count' => fn ($q) => $q->whereDate('created_at', Carbon::today()),
        ])->find($id);

        return $outlet;
    }

    /**
     * Create new outlet.
     */
    public function create(array $data, ?User $user = null): Outlet
    {
        $effUser = $user ?? auth()->user();
        $restaurantId = $data['restaurant_id'] ?? $effUser?->restaurant_id ?? Restaurant::first()?->id ?? 1;

        // Check unique code per restaurant
        if (Outlet::where('restaurant_id', $restaurantId)->where('code', $data['code'])->exists()) {
            throw ValidationException::withMessages([
                'code' => ['Kode outlet ini sudah digunakan pada restoran ini.'],
            ]);
        }

        return DB::transaction(function () use ($data, $restaurantId, $effUser) {
            $outlet = Outlet::create([
                'restaurant_id' => $restaurantId,
                'name' => $data['name'],
                'code' => strtoupper($data['code']),
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? 'active',
            ]);

            // Assign creator if user exists
            if ($effUser) {
                $outlet->users()->syncWithoutDetaching([$effUser->id]);
            }

            // Audit log
            $this->auditLogService->log(
                action: 'create_outlet',
                description: "Menambahkan cabang/outlet baru: {$outlet->name} ({$outlet->code}).",
                auditable: $outlet,
                newValues: [
                    'id' => $outlet->id,
                    'name' => $outlet->name,
                    'code' => $outlet->code,
                    'status' => $outlet->status,
                ],
                userId: $effUser?->id,
                outletId: $outlet->id,
                module: 'Outlet'
            );

            return $outlet->load('restaurant');
        });
    }

    /**
     * Update outlet details.
     */
    public function update(Outlet $outlet, array $data, ?User $user = null): Outlet
    {
        $effUser = $user ?? auth()->user();

        // Check unique code if modified
        if (isset($data['code']) && $data['code'] !== $outlet->code) {
            $exists = Outlet::where('restaurant_id', $outlet->restaurant_id)
                ->where('code', $data['code'])
                ->where('id', '!=', $outlet->id)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'code' => ['Kode outlet ini sudah digunakan oleh outlet lain.'],
                ]);
            }
        }

        $oldValues = [
            'name' => $outlet->name,
            'code' => $outlet->code,
            'status' => $outlet->status,
            'phone' => $outlet->phone,
            'address' => $outlet->address,
        ];

        DB::transaction(function () use ($outlet, $data, $oldValues, $effUser) {
            $outlet->update([
                'name' => $data['name'] ?? $outlet->name,
                'code' => isset($data['code']) ? strtoupper($data['code']) : $outlet->code,
                'address' => array_key_exists('address', $data) ? $data['address'] : $outlet->address,
                'phone' => array_key_exists('phone', $data) ? $data['phone'] : $outlet->phone,
                'status' => $data['status'] ?? $outlet->status,
            ]);

            $newValues = [
                'name' => $outlet->name,
                'code' => $outlet->code,
                'status' => $outlet->status,
                'phone' => $outlet->phone,
                'address' => $outlet->address,
            ];

            $this->auditLogService->log(
                action: 'update_outlet',
                description: "Memperbarui data cabang/outlet: {$outlet->name} ({$outlet->code}).",
                auditable: $outlet,
                oldValues: $oldValues,
                newValues: $newValues,
                userId: $effUser?->id,
                outletId: $outlet->id,
                module: 'Outlet'
            );
        });

        return $outlet;
    }

    /**
     * Delete or deactivate outlet.
     */
    public function delete(Outlet $outlet, ?User $user = null): bool
    {
        $effUser = $user ?? auth()->user();

        // Prevent deleting outlet if it has associated orders
        if ($outlet->orders()->exists()) {
            throw ValidationException::withMessages([
                'outlet' => ['Outlet ini memiliki riwayat pesanan aktif dan tidak dapat dihapus. Anda dapat mengubah statusnya menjadi non-aktif.'],
            ]);
        }

        $name = $outlet->name;
        $code = $outlet->code;

        return DB::transaction(function () use ($outlet, $name, $code, $effUser) {
            $outlet->users()->detach();
            $deleted = $outlet->delete();

            $this->auditLogService->log(
                action: 'delete_outlet',
                description: "Menghapus outlet {$name} ({$code}).",
                auditable: null,
                oldValues: ['name' => $name, 'code' => $code],
                userId: $effUser?->id,
                outletId: null,
                module: 'Outlet'
            );

            return (bool) $deleted;
        });
    }

    /**
     * Assign employees to an outlet.
     */
    public function assignUsers(Outlet $outlet, array $userIds, ?User $user = null): Outlet
    {
        $effUser = $user ?? auth()->user();

        DB::transaction(function () use ($outlet, $userIds, $effUser) {
            $outlet->users()->sync($userIds);

            $this->auditLogService->log(
                action: 'assign_outlet',
                description: "Menetapkan " . count($userIds) . " karyawan ke outlet {$outlet->name}.",
                auditable: $outlet,
                newValues: ['user_ids' => $userIds],
                userId: $effUser?->id,
                outletId: $outlet->id,
                module: 'Outlet'
            );
        });

        return $outlet->load(['users.roles']);
    }
}
