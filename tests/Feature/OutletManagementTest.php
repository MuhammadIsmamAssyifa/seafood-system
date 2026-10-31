<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderTable;
use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class OutletManagementTest extends TestCase
{
    protected User $owner;
    protected User $admin;
    protected User $cashier;
    protected Restaurant $restaurant;
    protected Outlet $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
        (new PermissionSeeder)->run();

        $this->restaurant = Restaurant::firstOrCreate(
            ['code' => 'SEAFOOD-OM'],
            ['name' => 'Resto Outlet Test', 'status' => 'active']
        );

        $this->outlet = Outlet::firstOrCreate(
            ['code' => 'OUT-OM-01'],
            ['restaurant_id' => $this->restaurant->id, 'name' => 'Outlet Utama OM', 'status' => 'active']
        );

        $this->owner = User::firstOrCreate(
            ['email' => 'owner_om@seafood.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Owner Outlet',
                'status' => 'active',
                'password' => bcrypt('password123'),
            ]
        );
        $this->owner->syncRoles(['owner']);
        $this->owner->outlets()->sync([$this->outlet->id]);

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_om@seafood.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Admin Outlet',
                'status' => 'active',
                'password' => bcrypt('password123'),
            ]
        );
        $this->admin->syncRoles(['admin']);
        $this->admin->outlets()->sync([$this->outlet->id]);

        $this->cashier = User::firstOrCreate(
            ['email' => 'cashier_om@seafood.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Cashier Outlet',
                'status' => 'active',
                'pin' => '4455',
            ]
        );
        $this->cashier->syncRoles(['cashier']);
        $this->cashier->outlets()->sync([$this->outlet->id]);

        // Cleanup any previous created test outlets for BDG
        Outlet::where('code', 'BDG-01')->delete();
    }

    public function test_can_list_outlets_with_counts(): void
    {
        $response = $this->actingAs($this->owner, 'sanctum')->getJson('/api/v1/outlets');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'code',
                        'status',
                        'users_count',
                        'menus_count',
                        'ingredients_count',
                    ],
                ],
            ]);
    }

    public function test_owner_can_create_outlet(): void
    {
        $payload = [
            'name' => 'Seafood Bahari Bandung',
            'code' => 'BDG-01',
            'address' => 'Jl. Asia Afrika No. 20',
            'phone' => '022-1234567',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->owner, 'sanctum')->postJson('/api/v1/outlets', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Seafood Bahari Bandung')
            ->assertJsonPath('data.code', 'BDG-01');

        $this->assertDatabaseHas('outlets', [
            'code' => 'BDG-01',
            'name' => 'Seafood Bahari Bandung',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'create_outlet',
            'module' => 'Outlet',
        ]);
    }

    public function test_rejects_duplicate_outlet_code_in_same_restaurant(): void
    {
        $payload = [
            'name' => 'Seafood Duplicate Code',
            'code' => $this->outlet->code, // duplicate
            'address' => 'Jl. Tes',
        ];

        $response = $this->actingAs($this->owner, 'sanctum')->postJson('/api/v1/outlets', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    public function test_owner_can_update_outlet_details_and_status(): void
    {
        $payload = [
            'name' => 'Outlet Utama Renamed',
            'code' => $this->outlet->code,
            'address' => 'Jl. Dermaga Baru',
            'phone' => '0812-9999-0000',
            'status' => 'inactive',
        ];

        $response = $this->actingAs($this->owner, 'sanctum')->putJson("/api/v1/outlets/{$this->outlet->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Outlet Utama Renamed')
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('outlets', [
            'id' => $this->outlet->id,
            'name' => 'Outlet Utama Renamed',
            'status' => 'inactive',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'update_outlet',
            'module' => 'Outlet',
        ]);
    }

    public function test_owner_can_assign_employees_to_outlet(): void
    {
        $newStaff = User::firstOrCreate(
            ['email' => 'staff_baru_om@seafood.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Staff Baru OM',
                'status' => 'active',
                'pin' => '7788',
            ]
        );

        $payload = [
            'user_ids' => [$this->owner->id, $newStaff->id],
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/v1/outlets/{$this->outlet->id}/assign-users", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('user_outlets', [
            'outlet_id' => $this->outlet->id,
            'user_id' => $newStaff->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'assign_outlet',
            'module' => 'Outlet',
        ]);
    }

    public function test_deleting_outlet_with_active_orders_is_prevented(): void
    {
        $table = OrderTable::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'number' => 'OM-TBL-1'],
            ['status' => 'available']
        );

        // Create an order in this outlet
        Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $table->id,
            'order_number' => 'ORD-OM-' . uniqid(),
            'customer_name' => 'Budi OM',
            'status' => 'completed',
            'subtotal' => 50000,
            'grand_total' => 50000,
            'created_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')->deleteJson("/api/v1/outlets/{$this->outlet->id}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors('outlet');
    }

    public function test_cashier_is_forbidden_from_managing_outlets(): void
    {
        $postRes = $this->actingAs($this->cashier, 'sanctum')->postJson('/api/v1/outlets', [
            'name' => 'Kasir Buat Outlet',
            'code' => 'KSR-01',
        ]);
        $postRes->assertStatus(403);

        $delRes = $this->actingAs($this->cashier, 'sanctum')->deleteJson("/api/v1/outlets/{$this->outlet->id}");
        $delRes->assertStatus(403);
    }
}
