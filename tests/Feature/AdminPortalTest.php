<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class AdminPortalTest extends TestCase
{
    protected User $owner;
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
            ['code' => 'OUTLET-T1'],
            ['restaurant_id' => $restaurant->id, 'name' => 'Outlet Test', 'status' => 'active']
        );

        $this->owner = User::firstOrCreate(
            ['email' => 'owner_portal@test.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Owner Portal Test',
                'username' => 'ownerportal',
                'password' => bcrypt('password123'),
                'status' => 'active',
            ]
        );

        $this->owner->syncRoles(['owner']);

        if (! $this->owner->outlets()->where('outlets.id', $this->outlet->id)->exists()) {
            $this->owner->outlets()->attach($this->outlet->id);
        }
    }

    public function test_admin_spa_view_renders_successfully(): void
    {
        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('admin-app');
        $response->assertSee('window.__APP_CONFIG__', false);

        $responseLogin = $this->get('/admin/login');
        $responseLogin->assertStatus(200);
        $responseLogin->assertSee('admin-app');
    }

    public function test_login_with_email_and_username(): void
    {
        // 1. Login with email
        $responseEmail = $this->postJson('/api/v1/login', [
            'email' => 'owner_portal@test.com',
            'password' => 'password123',
        ]);

        $responseEmail->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email', 'status', 'roles'],
            ]);

        // 2. Login with username
        $responseUsername = $this->postJson('/api/v1/login', [
            'username' => 'ownerportal',
            'password' => 'password123',
        ]);

        $responseUsername->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'username', 'status', 'roles'],
            ]);
    }

    public function test_dashboard_overview_endpoint_returns_aggregated_metrics(): void
    {
        $response = $this->actingAs($this->owner)
            ->getJson('/api/v1/dashboard/overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'outlet_id',
                    'date',
                    'sales' => [
                        'today_revenue',
                        'today_cogs',
                        'today_gross_profit',
                        'today_profit_margin',
                        'average_transaction',
                    ],
                    'orders' => [
                        'total_today',
                        'completed_today',
                        'active_orders',
                        'guests_today',
                        'status_breakdown',
                    ],
                    'payments' => [
                        'today_cash',
                        'today_transfer',
                        'today_qris',
                        'total_collected',
                    ],
                    'tables' => [
                        'total_tables',
                        'occupied_tables',
                        'available_tables',
                        'occupancy_rate',
                    ],
                    'inventory_alerts' => [
                        'low_stock_count',
                        'low_stock_items',
                    ],
                    'recent_orders',
                    'top_selling_menus',
                ],
            ]);
    }

    public function test_unauthenticated_request_to_dashboard_overview_is_unauthorized(): void
    {
        $response = $this->getJson('/api/v1/dashboard/overview');
        $response->assertStatus(401);
    }

    public function test_sales_report_endpoint_returns_aggregated_metrics_and_comparison(): void
    {
        $response = $this->actingAs($this->owner)
            ->getJson('/api/v1/reports/sales?start_date=' . now()->subDays(7)->toDateString() . '&end_date=' . now()->toDateString());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'outlet_id',
                    'start_date',
                    'end_date',
                    'summary' => [
                        'total_orders',
                        'total_guests',
                        'gross_sales',
                        'total_discounts',
                        'net_sales',
                        'total_sales',
                        'total_cogs',
                        'gross_profit',
                        'profit_margin',
                        'average_order_value',
                    ],
                    'comparison' => [
                        'sales_growth',
                        'orders_growth',
                    ],
                    'daily_trends',
                    'top_menus',
                ],
            ]);
    }

    public function test_orders_index_with_filters(): void
    {
        $response = $this->actingAs($this->owner)
            ->getJson('/api/v1/orders?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'order_number',
                        'customer_name',
                        'status',
                        'grand_total',
                    ],
                ],
                'links',
                'meta' => [
                    'current_page',
                    'last_page',
                    'total',
                ],
            ]);
    }

    public function test_order_detail_endpoint_loads_complete_relations(): void
    {
        $table = \App\Models\OrderTable::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'number' => 'T-PORTAL'],
            ['status' => 'available']
        );

        $order = \App\Models\Order::firstOrCreate(
            ['order_number' => 'ORD-PORTAL-TEST'],
            [
                'outlet_id' => $this->outlet->id,
                'order_table_id' => $table->id,
                'customer_name' => 'Budi Testing',
                'guest_count' => 2,
                'status' => 'completed',
                'subtotal' => 150000,
                'discount' => 0,
                'grand_total' => 150000,
                'total_cogs' => 60000,
                'gross_profit' => 90000,
                'created_by' => $this->owner->id,
            ]
        );

        $response = $this->actingAs($this->owner)
            ->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_number',
                    'customer_name',
                    'status',
                    'grand_total',
                    'table',
                    'batches',
                    'payments',
                    'status_histories',
                ],
            ]);
    }

    public function test_order_detail_not_found_returns_404(): void
    {
        $response = $this->actingAs($this->owner)
            ->getJson('/api/v1/orders/9999999');

        $response->assertStatus(404);
    }

    public function test_inventory_list_and_summary_endpoint(): void
    {
        $ingredient = \App\Models\Ingredient::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Ikan Kakap Merah'],
            ['unit' => 'kg', 'status' => 'active']
        );

        $response = $this->actingAs($this->owner)
            ->getJson('/api/v1/inventory');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'unit',
                        'current_stock',
                        'minimum_stock',
                        'status',
                    ],
                ],
                'summary' => [
                    'total_ingredients',
                    'available_count',
                    'low_stock_count',
                    'out_of_stock_count',
                    'total_stock_quantity',
                ],
            ]);
    }

    public function test_inventory_stock_adjustment_in_and_out(): void
    {
        $ingredient = \App\Models\Ingredient::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Kepiting Bakau'],
            ['unit' => 'kg', 'status' => 'active']
        );

        // 1. In adjustment
        $responseIn = $this->actingAs($this->owner)
            ->postJson('/api/v1/inventory/adjustments', [
                'ingredient_id' => $ingredient->id,
                'type' => 'in',
                'quantity' => 10.5,
                'cost_per_unit' => 95000,
                'note' => 'Restock pasar ikan',
            ]);

        $responseIn->assertStatus(200)
            ->assertJsonPath('data.ingredient_id', $ingredient->id);

        // 2. Out adjustment
        $responseOut = $this->actingAs($this->owner)
            ->postJson('/api/v1/inventory/adjustments', [
                'ingredient_id' => $ingredient->id,
                'type' => 'out',
                'quantity' => 2.5,
                'note' => 'Koreksi timbangan',
            ]);

        $responseOut->assertStatus(200);

        // 3. Out adjustment excessive returns 422
        $responseExcess = $this->actingAs($this->owner)
            ->postJson('/api/v1/inventory/adjustments', [
                'ingredient_id' => $ingredient->id,
                'type' => 'out',
                'quantity' => 9999,
            ]);

        $responseExcess->assertStatus(422);
    }

    public function test_inventory_cost_summary_and_detail_endpoints(): void
    {
        $ingredient = \App\Models\Ingredient::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Udang Windu Super'],
            ['unit' => 'kg', 'status' => 'active']
        );

        // Add a batch
        $this->actingAs($this->owner)->postJson('/api/v1/inventory/adjustments', [
            'ingredient_id' => $ingredient->id,
            'type' => 'in',
            'quantity' => 5,
            'cost_per_unit' => 120000,
            'note' => 'Batch 1',
        ]);

        // 1. Cost Summary
        $responseSummary = $this->actingAs($this->owner)
            ->getJson('/api/v1/inventory/cost');

        $responseSummary->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_stock_value',
                    'ingredient_count',
                    'active_batches_count',
                    'ingredients' => [
                        '*' => [
                            'id',
                            'name',
                            'unit',
                            'current_stock',
                            'average_cost',
                            'total_value',
                        ],
                    ],
                ],
            ]);

        // 2. Cost Detail for ingredient
        $responseDetail = $this->actingAs($this->owner)
            ->getJson("/api/v1/inventory/cost/{$ingredient->id}");

        $responseDetail->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'ingredient' => ['id', 'name', 'unit'],
                    'stock',
                    'total_stock_value',
                    'average_cost',
                    'batches' => [
                        '*' => [
                            'id',
                            'batch_number',
                            'quantity',
                            'remaining_quantity',
                            'cost_per_unit',
                            'value',
                        ],
                    ],
                    'cost_history',
                ],
            ]);
    }

    public function test_waste_management_endpoints(): void
    {
        $ingredient = \App\Models\Ingredient::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Cumi Waste Portal Test ' . uniqid(),
            'unit' => \App\Enums\IngredientUnit::Kg,
            'status' => \App\Enums\ActiveStatus::Active,
        ]);

        \App\Models\InventoryBatch::create([
            'outlet_id' => $this->outlet->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 5,
            'remaining_quantity' => 5,
            'cost_per_unit' => 60000,
            'received_at' => now(),
        ]);

        // Create Waste
        $resStore = $this->actingAs($this->owner)
            ->postJson('/api/v1/waste', [
                'ingredient_id' => $ingredient->id,
                'quantity' => 2,
                'reason' => 'damaged',
                'note' => 'Rusak saat delivery',
            ]);

        $resStore->assertStatus(201)
            ->assertJsonPath('data.quantity', 2)
            ->assertJsonPath('data.total_cost', 120000);

        // Waste Summary
        $resSummary = $this->actingAs($this->owner)
            ->getJson('/api/v1/waste/summary');

        $resSummary->assertStatus(200)
            ->assertJsonStructure(['total_cost', 'total_quantity', 'reason_breakdown']);

        // Waste Index
        $resIndex = $this->actingAs($this->owner)
            ->getJson('/api/v1/waste');

        $resIndex->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_employee_and_role_management_endpoints(): void
    {
        // 1. Employee list
        $resEmpList = $this->actingAs($this->owner)
            ->getJson('/api/v1/employees');
        $resEmpList->assertStatus(200)
            ->assertJsonStructure(['data', 'meta', 'summary']);

        // 2. Roles list & matrix
        $resRoles = $this->actingAs($this->owner)
            ->getJson('/api/v1/roles');
        $resRoles->assertStatus(200)
            ->assertJsonStructure(['data']);

        $resMatrix = $this->actingAs($this->owner)
            ->getJson('/api/v1/roles/matrix');
        $resMatrix->assertStatus(200)
            ->assertJsonStructure(['roles', 'matrix']);
    }
}

