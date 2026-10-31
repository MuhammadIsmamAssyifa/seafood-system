<?php

namespace Tests\Feature;

use App\Enums\IngredientUnit;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderTable;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\InventoryService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    protected User $owner;

    protected User $staff;

    protected Outlet $outlet;

    protected OrderTable $table;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
        (new PermissionSeeder)->run();

        $restaurant = Restaurant::firstOrCreate(
            ['code' => 'SEAFOOD-RESTO'],
            ['name' => 'Seafood Resto', 'status' => 'active']
        );

        $this->outlet = Outlet::firstOrCreate(
            ['code' => 'OUTLET-01'],
            ['restaurant_id' => $restaurant->id, 'name' => 'Main Outlet', 'status' => 'active']
        );

        $this->owner = User::firstOrCreate(
            ['email' => 'owner_audit@seafood.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Owner Audit',
                'status' => 'active',
                'password' => bcrypt('password123'),
            ]
        );
        $this->owner->syncRoles(['owner']);
        $this->owner->outlets()->sync([$this->outlet->id]);

        $this->staff = User::firstOrCreate(
            ['email' => 'staff_no_perm@seafood.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Staff No Perm',
                'status' => 'active',
                'pin' => '9988',
            ]
        );
        $this->staff->syncRoles(['kitchen']);
        $this->staff->outlets()->sync([$this->outlet->id]);

        $this->table = OrderTable::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'number' => 'AL-01'],
            ['status' => 'available']
        );
    }

    public function test_login_creates_audit_log(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => $this->owner->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login',
            'user_id' => $this->owner->id,
            'outlet_id' => $this->outlet->id,
        ]);
    }

    public function test_order_creation_triggers_audit_log(): void
    {
        $this->actingAs($this->owner);

        $table = OrderTable::create([
            'outlet_id' => $this->outlet->id,
            'number' => 'AL-'.uniqid(),
            'status' => 'available',
        ]);

        $orderService = app(OrderService::class);
        $order = $orderService->createDraft([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $table->id,
            'customer_name' => 'Budi Santoso',
            'guest_count' => 3,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'create_order',
            'auditable_type' => Order::class,
            'auditable_id' => $order->id,
            'user_id' => $this->owner->id,
        ]);
    }

    public function test_payment_triggers_audit_log(): void
    {
        $this->actingAs($this->owner);

        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table->id,
            'order_number' => 'ORD-'.date('YmdHis').'-TEST',
            'customer_name' => 'Siti',
            'guest_count' => 2,
            'status' => 'draft',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'created_by' => $this->owner->id,
        ]);

        $paymentService = app(PaymentService::class);
        $payment = $paymentService->createPayment($order, [
            'method' => 'cash',
            'amount' => 100000,
            'received_amount' => 100000,
            'change_amount' => 0,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'payment',
            'auditable_type' => Payment::class,
            'auditable_id' => $payment->id,
        ]);
    }

    public function test_inventory_adjustment_triggers_audit_log(): void
    {
        $this->actingAs($this->owner);

        $ingredient = Ingredient::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Kepiting Bakau AL'],
            ['unit' => IngredientUnit::Kg]
        );

        $inventoryService = app(InventoryService::class);
        $inventoryService->addStock([
            'outlet_id' => $this->outlet->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 5,
            'cost_per_unit' => 120000,
            'note' => 'Restock mingguan',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'inventory_adjustment',
            'auditable_type' => StockMovement::class,
        ]);
    }

    public function test_authorized_user_can_query_audit_logs_resource(): void
    {
        $auditLogService = app(AuditLogService::class);
        $auditLogService->log(
            action: 'price_change',
            description: 'Penyesuaian timbangan ikan kakap',
            userId: $this->owner->id,
            outletId: $this->outlet->id
        );

        $response = $this->actingAs($this->owner)
            ->getJson('/api/v1/audit-logs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'outlet_id',
                        'outlet_name',
                        'user' => ['id', 'name', 'email'],
                        'action',
                        'description',
                        'auditable_type',
                        'auditable_id',
                        'old_values',
                        'new_values',
                        'ip_address',
                        'user_agent',
                        'created_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_audit_logs_can_be_filtered_by_action(): void
    {
        $auditLogService = app(AuditLogService::class);
        $auditLogService->log(
            action: 'custom_filter_action',
            description: 'Test filtering action',
            userId: $this->owner->id,
            outletId: $this->outlet->id
        );

        $response = $this->actingAs($this->owner)
            ->getJson('/api/v1/audit-logs?action=custom_filter_action');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertEquals('custom_filter_action', $item['action']);
        }
    }

    public function test_unauthorized_user_is_forbidden_from_viewing_audit_logs(): void
    {
        // Staff role kitchen tidak memiliki permission report.view maupun user.manage
        $response = $this->actingAs($this->staff)
            ->getJson('/api/v1/audit-logs');

        $response->assertStatus(403);
    }
}
