<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();
        $restaurantId = $authUser->restaurant_id;

        $query = User::query()
            ->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))
            ->with(['roles', 'outlets'])
            ->latest('id');

        // Search
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('username', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Filter by Role
        if ($request->filled('role')) {
            $role = $request->query('role');
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Filter by Outlet
        if ($request->filled('outlet_id')) {
            $outletId = $request->query('outlet_id');
            $query->whereHas('outlets', fn ($q) => $q->where('outlets.id', $outletId));
        }

        $perPage = (int) $request->query('per_page', 15);
        $paginated = $query->paginate($perPage);

        // Overall summary
        $baseSummaryQuery = User::query()
            ->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId));

        $totalEmployees = (clone $baseSummaryQuery)->count();
        $activeCount = (clone $baseSummaryQuery)->where('status', 'active')->count();
        $inactiveCount = (clone $baseSummaryQuery)->where('status', 'inactive')->count();

        $roleCounts = [
            'owner' => (clone $baseSummaryQuery)->whereHas('roles', fn ($q) => $q->where('name', 'owner'))->count(),
            'admin' => (clone $baseSummaryQuery)->whereHas('roles', fn ($q) => $q->where('name', 'admin'))->count(),
            'cashier' => (clone $baseSummaryQuery)->whereHas('roles', fn ($q) => $q->whereIn('name', ['cashier', 'kasir']))->count(),
            'kitchen' => (clone $baseSummaryQuery)->whereHas('roles', fn ($q) => $q->whereIn('name', ['kitchen', 'kitchen_head', 'kitchen_staff']))->count(),
        ];

        $items = collect($paginated->items())->map(fn (User $user) => $this->formatUser($user));

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
            'summary' => [
                'total' => $totalEmployees,
                'active' => $activeCount,
                'inactive' => $inactiveCount,
                'role_counts' => $roleCounts,
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $authUser = $request->user();
        $restaurantId = $authUser->restaurant_id;

        $user = User::query()
            ->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))
            ->with(['roles.permissions', 'outlets', 'restaurant'])
            ->find($id);

        if (! $user) {
            return response()->json(['message' => 'Karyawan tidak ditemukan.'], 404);
        }

        return response()->json([
            'data' => $this->formatUser($user, true),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'pin' => ['nullable', 'string', 'digits_between:4,6'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'outlet_ids' => ['nullable', 'array'],
            'outlet_ids.*' => ['exists:outlets,id'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ], [
            'username.unique' => 'Username sudah digunakan.',
            'email.unique' => 'Email sudah digunakan.',
            'role.exists' => 'Role tidak tersedia.',
            'outlet_ids.*.exists' => 'Outlet tidak tersedia.',
        ]);

        $authUser = $request->user();
        $restaurantId = $authUser->restaurant_id ?? 1;

        $user = DB::transaction(function () use ($validated, $restaurantId, $authUser) {
            $user = User::create([
                'restaurant_id' => $restaurantId,
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'pin_hash' => ! empty($validated['pin']) ? Hash::make($validated['pin']) : null,
                'status' => $validated['status'] ?? 'active',
            ]);

            $user->syncRoles([$validated['role']]);

            $outlets = ! empty($validated['outlet_ids'])
                ? $validated['outlet_ids']
                : $authUser->outlets()->pluck('outlets.id')->toArray();

            if (! empty($outlets)) {
                $user->outlets()->sync($outlets);
            }

            $this->auditLogService->log(
                action: 'employee_created',
                description: "Menambahkan karyawan baru: {$user->name} ({$user->username}) dengan role {$validated['role']}.",
                auditable: $user,
                newValues: [
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $validated['role'],
                    'status' => $user->status,
                ],
                userId: $authUser->id,
                outletId: $outlets[0] ?? null
            );

            return $user;
        });

        $user->load(['roles', 'outlets']);

        return response()->json([
            'message' => 'Karyawan berhasil didaftarkan.',
            'data' => $this->formatUser($user),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $authUser = $request->user();
        $restaurantId = $authUser->restaurant_id;

        $user = User::query()
            ->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))
            ->find($id);

        if (! $user) {
            return response()->json(['message' => 'Karyawan tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'password' => ['nullable', 'string', 'min:6'],
            'pin' => ['nullable', 'string', 'digits_between:4,6'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'outlet_ids' => ['nullable', 'array'],
            'outlet_ids.*' => ['exists:outlets,id'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ], [
            'username.unique' => 'Username sudah digunakan.',
            'email.unique' => 'Email sudah digunakan.',
            'role.exists' => 'Role tidak tersedia.',
            'outlet_ids.*.exists' => 'Outlet tidak tersedia.',
        ]);

        DB::transaction(function () use ($user, $validated, $authUser) {
            $user->name = $validated['name'];
            $user->username = $validated['username'];
            $user->email = $validated['email'];

            if (! empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            if (isset($validated['pin'])) {
                $user->pin_hash = ! empty($validated['pin']) ? Hash::make($validated['pin']) : null;
            }

            if (! empty($validated['status'])) {
                $user->status = $validated['status'];
            }

            $user->save();

            $user->syncRoles([$validated['role']]);

            if (isset($validated['outlet_ids'])) {
                $user->outlets()->sync($validated['outlet_ids']);
            }

            $this->auditLogService->log(
                action: 'employee_updated',
                description: "Memperbarui profil karyawan: {$user->name} ({$user->username}).",
                auditable: $user,
                newValues: [
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $validated['role'],
                    'status' => $user->status,
                ],
                userId: $authUser->id,
                outletId: $user->outlets()->first()?->id
            );
        });

        $user->load(['roles', 'outlets']);

        return response()->json([
            'message' => 'Data karyawan berhasil diperbarui.',
            'data' => $this->formatUser($user),
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $authUser = $request->user();
        if ($authUser->id === $id) {
            return response()->json(['message' => 'Anda tidak dapat mengubah status akun Anda sendiri.'], 422);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $user = User::query()
            ->when($authUser->restaurant_id, fn ($q) => $q->where('restaurant_id', $authUser->restaurant_id))
            ->find($id);

        if (! $user) {
            return response()->json(['message' => 'Karyawan tidak ditemukan.'], 404);
        }

        $oldStatus = $user->status;
        $user->status = $validated['status'];
        $user->save();

        $this->auditLogService->log(
            action: 'employee_status_changed',
            description: "Mengubah status karyawan {$user->name} dari {$oldStatus} menjadi {$user->status}.",
            auditable: $user,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $user->status],
            userId: $authUser->id,
            outletId: $user->outlets()->first()?->id
        );

        return response()->json([
            'message' => "Status karyawan berhasil diubah menjadi {$user->status}.",
            'data' => [
                'id' => $user->id,
                'status' => $user->status,
            ],
        ]);
    }

    public function assignOutlets(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'outlet_ids' => ['required', 'array'],
            'outlet_ids.*' => ['exists:outlets,id'],
        ]);

        $authUser = $request->user();
        $user = User::query()
            ->when($authUser->restaurant_id, fn ($q) => $q->where('restaurant_id', $authUser->restaurant_id))
            ->find($id);

        if (! $user) {
            return response()->json(['message' => 'Karyawan tidak ditemukan.'], 404);
        }

        $user->outlets()->sync($validated['outlet_ids']);

        $this->auditLogService->log(
            action: 'employee_outlets_assigned',
            description: "Memperbarui penugasan outlet untuk karyawan {$user->name}.",
            auditable: $user,
            userId: $authUser->id,
            outletId: $validated['outlet_ids'][0] ?? null
        );

        $user->load('outlets');

        return response()->json([
            'message' => 'Penugasan outlet karyawan berhasil diperbarui.',
            'data' => [
                'id' => $user->id,
                'outlets' => $user->outlets->map(fn ($o) => ['id' => $o->id, 'name' => $o->name, 'code' => $o->code]),
            ],
        ]);
    }

    public function metaOptions(Request $request): JsonResponse
    {
        $authUser = $request->user();
        $restaurantId = $authUser->restaurant_id;

        $roles = Role::all()->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'label' => ucfirst(str_replace('_', ' ', $r->name)),
        ]);

        $outlets = Outlet::query()
            ->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))
            ->where('status', 'active')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'roles' => $roles,
            'outlets' => $outlets,
        ]);
    }

    protected function formatUser(User $user, bool $includeDetails = false): array
    {
        $roleName = $user->getRoleNames()->first() ?? '-';
        $outlets = $user->outlets->map(fn ($o) => [
            'id' => $o->id,
            'name' => $o->name,
            'code' => $o->code,
        ]);

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'status' => $user->status,
            'role' => $roleName,
            'roles' => $user->getRoleNames(),
            'outlet' => $outlets->first() ?? null,
            'outlets' => $outlets,
            'has_pin' => ! empty($user->pin_hash),
            'created_at' => $user->created_at?->toISOString(),
        ];

        if ($includeDetails) {
            $data['permissions'] = $user->getAllPermissions()->pluck('name');
            $data['restaurant'] = $user->restaurant ? [
                'id' => $user->restaurant->id,
                'name' => $user->restaurant->name,
            ] : null;
        }

        return $data;
    }
}

