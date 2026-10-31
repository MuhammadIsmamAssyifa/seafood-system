<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $roles = Role::with('permissions')->get()->map(function (Role $role) {
            $userCount = User::role($role->name)->count();

            return [
                'id' => $role->id,
                'name' => $role->name,
                'label' => ucfirst(str_replace('_', ' ', $role->name)),
                'user_count' => $userCount,
                'permission_count' => $role->permissions->count(),
                'permissions' => $role->permissions->pluck('name'),
                'is_system' => in_array($role->name, ['owner', 'admin', 'cashier', 'kitchen'], true),
            ];
        });

        return response()->json([
            'data' => $roles,
        ]);
    }

    public function permissions(Request $request): JsonResponse
    {
        $allPermissions = Permission::orderBy('name')->get();

        $grouped = [
            'Order & Kasir' => [],
            'Kitchen KDS' => [],
            'Menu & Katalog' => [],
            'Inventaris & Waste' => [],
            'Karyawan & Akun' => [],
            'Role & Hak Akses' => [],
            'Laporan & Analitik' => [],
        ];

        foreach ($allPermissions as $perm) {
            $name = $perm->name;
            $cat = match (true) {
                str_starts_with($name, 'order.') || str_starts_with($name, 'payment.') => 'Order & Kasir',
                str_starts_with($name, 'kitchen.') => 'Kitchen KDS',
                str_starts_with($name, 'menu.') => 'Menu & Katalog',
                str_starts_with($name, 'inventory.') || str_starts_with($name, 'waste.') => 'Inventaris & Waste',
                str_starts_with($name, 'employee.') || str_starts_with($name, 'user.') => 'Karyawan & Akun',
                str_starts_with($name, 'role.') => 'Role & Hak Akses',
                default => 'Laporan & Analitik',
            };

            $grouped[$cat][] = [
                'id' => $perm->id,
                'name' => $name,
                'label' => $this->formatPermissionLabel($name),
            ];
        }

        return response()->json([
            'data' => $allPermissions->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'label' => $this->formatPermissionLabel($p->name),
            ]),
            'grouped' => $grouped,
        ]);
    }

    public function matrix(Request $request): JsonResponse
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::orderBy('name')->get();

        $matrix = $permissions->map(function ($perm) use ($roles) {
            $roleAssignments = [];
            foreach ($roles as $role) {
                $roleAssignments[$role->name] = $role->hasPermissionTo($perm->name);
            }

            return [
                'permission' => $perm->name,
                'label' => $this->formatPermissionLabel($perm->name),
                'roles' => $roleAssignments,
            ];
        });

        return response()->json([
            'roles' => $roles->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'label' => ucfirst(str_replace('_', ' ', $r->name))]),
            'matrix' => $matrix,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ], [
            'name.unique' => 'Nama role sudah terdaftar.',
        ]);

        $role = DB::transaction(function () use ($validated, $request) {
            $role = Role::create([
                'name' => strtolower(trim(str_replace(' ', '_', $validated['name']))),
                'guard_name' => 'web',
            ]);

            if (! empty($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            $this->auditLogService->log(
                action: 'role_created',
                description: "Membuat role baru {$role->name} dengan " . count($validated['permissions'] ?? []) . " permissions.",
                userId: $request->user()->id
            );

            return $role;
        });

        $role->load('permissions');

        return response()->json([
            'message' => "Role {$role->name} berhasil dibuat.",
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'owner') {
            return response()->json(['message' => 'Hak akses Role Owner bersifat permanen dan tidak dapat diubah.'], 422);
        }

        $validated = $request->validate([
            'permissions' => ['present', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        DB::transaction(function () use ($role, $validated, $request) {
            $role->syncPermissions($validated['permissions']);

            $this->auditLogService->log(
                action: 'role_permissions_updated',
                description: "Memperbarui permissions untuk role {$role->name}. Total: " . count($validated['permissions']) . " hak akses.",
                userId: $request->user()->id
            );
        });

        $role->load('permissions');

        return response()->json([
            'message' => "Hak akses role {$role->name} berhasil diperbarui.",
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
        ]);
    }

    protected function formatPermissionLabel(string $name): string
    {
        $labels = [
            'order.view' => 'Melihat Daftar Pesanan',
            'order.create' => 'Membuat Pesanan Baru',
            'order.update' => 'Mengubah Pesanan',
            'order.cancel' => 'Membatalkan Pesanan',
            'order.change' => 'Mengajukan Perubahan Item / Timbangan',
            'payment.view' => 'Melihat Transaksi Pembayaran',
            'payment.create' => 'Memproses Pembayaran Kasir',
            'kitchen.view' => 'Melihat Kitchen Display System (KDS)',
            'kitchen.update' => 'Mengubah Status Memasak / Saji',
            'menu.view' => 'Melihat Katalog Menu',
            'menu.manage' => 'Mengelola & Mengubah Menu',
            'inventory.view' => 'Melihat Stok Bahan Baku',
            'inventory.adjust' => 'Melakukan Penyesuaian Stok',
            'inventory.manage' => 'Mengelola Inventaris & Batch Cost',
            'waste.view' => 'Melihat Riwayat Bahan Terbuang',
            'waste.manage' => 'Mencatat & Menganalisis Waste',
            'employee.view' => 'Melihat Daftar Karyawan',
            'employee.create' => 'Mendaftarkan Karyawan Baru',
            'employee.update' => 'Mengubah Data Karyawan',
            'employee.manage' => 'Mengelola Karyawan & Penugasan Outlet',
            'user.view' => 'Melihat Data Akun Pengguna',
            'user.manage' => 'Mengelola Akun Pengguna',
            'role.view' => 'Melihat Matriks Role & Hak Akses',
            'role.manage' => 'Mengubah & Mengatur Hak Akses Role',
            'dashboard.view' => 'Melihat Dashboard Ringkasan',
            'report.view' => 'Melihat Laporan Keuangan & Penjualan',
        ];

        return $labels[$name] ?? ucwords(str_replace(['.', '_'], ' ', $name));
    }
}

