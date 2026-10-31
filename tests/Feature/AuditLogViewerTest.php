<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderTable;
use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class AuditLogViewerTest extends TestCase
{
    protected User $owner;
    protected User $admin;
    protected User $cashier;
    protected Outlet $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
        (new PermissionSeeder)->run();

        $restaurant = Restaurant::firstOrCreate(
            ['code' => 'SEAFOOD-TEST'],
            ['name' => 'Seafood Test Resto', 'status' => 'active']
        );

        $this->outlet = Outlet::firstOrCreate(
            ['code' => 'OUT-ALV-01'],
            ['restaurant_id' => $restaurant->id, 'name' => 'Audit Outlet', 'status' => 'active']
        );

        $this->owner = User::firstOrCreate(
            ['email' => 'owner_viewer@seafood.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Owner Viewer',
                'status' => 'active',
                'password' => bcrypt('password123'),
            ]
        );
        $this->owner->syncRoles(['owner']);
        $this->owner->outlets()->sync([$this->outlet->id]);

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_viewer@seafood.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Admin Viewer',
                'status' => 'active',
                'password' => bcrypt('password123'),
            ]
        );
        $this->admin->syncRoles(['admin']);
        $this->admin->outlets()->sync([$this->outlet->id]);

        $this->cashier = User::firstOrCreate(
            ['email' => 'cashier_viewer@seafood.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Cashier Viewer',
                'status' => 'active',
                'pin' => '1122',
            ]
        );
        $this->cashier->syncRoles(['cashier']);
        $this->cashier->outlets()->sync([$this->outlet->id]);
    }

    public function test_owner_can_fetch_audit_logs_with_pagination_and_filters(): void
    {
        // Create sample audit logs
        AuditLog::create([
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->owner->id,
            'action' => 'update_menu',
            'module' => 'Menu',
            'description' => 'Owner mengubah harga Udang Bakar',
            'old_values' => ['price' => 50000],
            'new_values' => ['price' => 60000],
        ]);

        AuditLog::create([
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->admin->id,
            'action' => 'stock_adjustment',
            'module' => 'Inventory',
            'description' => 'Admin menambahkan stok Cumi Segar',
            'old_values' => ['stock' => 10],
            'new_values' => ['stock' => 25],
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')->getJson('/api/v1/audit-logs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'outlet_id',
                        'user',
                        'action',
                        'module',
                        'description',
                        'old_values',
                        'new_values',
                        'created_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_audit_logs_can_be_filtered_by_action_and_module(): void
    {
        AuditLog::create([
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->owner->id,
            'action' => 'login',
            'module' => 'Employee & Auth',
            'description' => 'User logged in',
        ]);

        AuditLog::create([
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->owner->id,
            'action' => 'order_created',
            'module' => 'Order',
            'description' => 'Pesanan baru dibuat',
        ]);

        $resAction = $this->actingAs($this->owner, 'sanctum')
            ->getJson('/api/v1/audit-logs?action=login');

        $resAction->assertStatus(200);
        foreach ($resAction->json('data') as $item) {
            $this->assertEquals('login', $item['action']);
        }

        $resModule = $this->actingAs($this->owner, 'sanctum')
            ->getJson('/api/v1/audit-logs?module=Order');

        $resModule->assertStatus(200);
        foreach ($resModule->json('data') as $item) {
            $this->assertEquals('Order', $item['module']);
        }
    }

    public function test_audit_log_show_endpoint_returns_detail_with_diff(): void
    {
        $log = AuditLog::create([
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->owner->id,
            'action' => 'update_menu',
            'module' => 'Menu',
            'description' => 'Owner mengubah harga Kepiting Saus Padang',
            'old_values' => ['price' => 120000],
            'new_values' => ['price' => 135000],
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')->getJson("/api/v1/audit-logs/{$log->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $log->id)
            ->assertJsonPath('data.old_values.price', 120000)
            ->assertJsonPath('data.new_values.price', 135000)
            ->assertJsonPath('data.ip_address', '127.0.0.1');
    }

    public function test_audit_log_summary_endpoint(): void
    {
        AuditLog::create([
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->owner->id,
            'action' => 'create_order',
            'module' => 'Order',
            'description' => 'Order test summary',
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')->getJson('/api/v1/audit-logs/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_logs',
                    'today_logs',
                    'unique_users',
                    'top_actions',
                ],
            ]);
    }

    public function test_cashier_is_forbidden_from_viewing_audit_logs(): void
    {
        $response = $this->actingAs($this->cashier, 'sanctum')->getJson('/api/v1/audit-logs');
        $response->assertStatus(403);

        $summaryRes = $this->actingAs($this->cashier, 'sanctum')->getJson('/api/v1/audit-logs/summary');
        $summaryRes->assertStatus(403);
    }
}

