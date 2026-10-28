<?php

namespace Tests\Feature;

use App\Models\CookingMethod;
use App\Models\Ingredient;
use App\Models\KitchenStation;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\OrderBatch;
use App\Models\OrderItem;
use App\Models\OrderTable;
use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    protected User $user;
    protected Outlet $outlet;
    protected OrderTable $table;
    protected MenuVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        (new RoleSeeder())->run();
        (new PermissionSeeder())->run();

        $restaurant = Restaurant::firstOrCreate(
            ['code' => 'SEAFOOD-RESTO'],
            ['name' => 'Seafood Resto', 'status' => 'active']
        );

        $this->outlet = Outlet::firstOrCreate(
            ['code' => 'OUTLET-01'],
            ['restaurant_id' => $restaurant->id, 'name' => 'Main Outlet', 'status' => 'active']
        );

        $this->user = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Admin Test',
                'username' => 'admintest',
                'password' => bcrypt('password123'),
                'pin_hash' => bcrypt('1234'),
                'status' => 'active',
            ]
        );

        $this->user->syncRoles(['owner']);

        if (!$this->user->outlets()->where('outlets.id', $this->outlet->id)->exists()) {
            $this->user->outlets()->attach($this->outlet->id);
        }

        $this->table = OrderTable::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'number' => 'T-01'],
            ['status' => 'available']
        );
        $this->table->update(['status' => \App\Enums\TableStatus::Available]);

        $station = KitchenStation::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'code' => 'GRILL'],
            ['name' => 'Grill Station', 'status' => 'active']
        );

        $cooking = CookingMethod::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Bakar'],
            ['status' => 'active']
        );

        $ingredient = Ingredient::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Ikan Gurame'],
            ['unit' => 'kg', 'status' => 'active']
        );

        $menu = Menu::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Gurame Bakar'],
            ['type' => 'seafood', 'status' => 'active']
        );

        $this->variant = MenuVariant::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Gurame Bakar Porsi'],
            [
                'menu_id' => $menu->id,
                'cooking_method_id' => $cooking->id,
                'main_ingredient_id' => $ingredient->id,
                'kitchen_station_id' => $station->id,
                'price' => 50000,
                'sales_unit' => 'portion',
                'inventory_unit' => 'kg',
                'conversion_rate' => 0.6,
                'status' => 'active',
            ]
        );
    }

    public function test_auth_login(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email', 'status', 'roles'],
            ]);
    }

    public function test_catalog_menus_returns_resource(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/catalog/menus');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'type',
                        'variants' => [
                            '*' => [
                                'id',
                                'name',
                                'price',
                                'sales_unit',
                                'ingredient',
                                'station',
                                'cooking_method',
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_catalog_stations_returns_resource(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/catalog/stations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'code',
                        'status',
                    ]
                ]
            ]);
    }

    public function test_order_lifecycle(): void
    {
        $this->table->update(['status' => \App\Enums\TableStatus::Available]);

        // 1. Create order
        $createOrderRes = $this->actingAs($this->user)
            ->postJson('/api/v1/orders', [
                'order_table_id' => $this->table->id,
                'customer_name' => 'Pak Budi',
                'guest_count' => 3,
            ]);

        $createOrderRes->assertStatus(201)
            ->assertJsonPath('data.customer_name', 'Pak Budi');

        $orderId = $createOrderRes->json('data.id');

        // 2. Add item to order
        $order = Order::find($orderId);
        $addItemRes = $this->actingAs($this->user)
            ->postJson("/api/v1/orders/{$orderId}/items", [
                'menu_variant_id' => $this->variant->id,
                'quantity' => 2,
            ]);

        $addItemRes->assertStatus(200)
            ->assertJsonPath('data.subtotal', 100000);

        // 3. Submit batch to kitchen
        $batch = $order->batches()->first();
        $submitRes = $this->actingAs($this->user)
            ->postJson("/api/v1/order-batches/{$batch->id}/submit");

        $submitRes->assertStatus(200)
            ->assertJsonPath('data.status', 'sent');

        // 4. Kitchen view orders
        $kitchenOrdersRes = $this->actingAs($this->user)
            ->getJson('/api/v1/kitchen/orders');

        $kitchenOrdersRes->assertStatus(200);

        // 5. Kitchen accept batch
        $acceptRes = $this->actingAs($this->user)
            ->patchJson("/api/v1/kitchen/batches/{$batch->id}/accept");

        $acceptRes->assertStatus(200)
            ->assertJsonPath('data.status', 'cooking');

        // 6. Payment summary
        $summaryRes = $this->actingAs($this->user)
            ->getJson("/api/v1/orders/{$orderId}/payments/summary");

        $summaryRes->assertStatus(200)
            ->assertJsonPath('data.grand_total', 100000);
    }

    public function test_order_item_change_workflow(): void
    {
        $this->table->update(['status' => \App\Enums\TableStatus::Available]);

        // 1. Create order and item
        $createOrderRes = $this->actingAs($this->user)
            ->postJson('/api/v1/orders', [
                'order_table_id' => $this->table->id,
                'customer_name' => 'Bu Ani',
                'guest_count' => 2,
            ]);

        $createOrderRes->assertStatus(201);
        $orderId = $createOrderRes->json('data.id');

        $addItemRes = $this->actingAs($this->user)
            ->postJson("/api/v1/orders/{$orderId}/items", [
                'menu_variant_id' => $this->variant->id,
                'quantity' => 1,
            ]);

        $order = Order::find($orderId);
        $item = $order->items()->first();

        // 2. Request quantity change
        $changeRes = $this->actingAs($this->user)
            ->postJson("/api/v1/order-items/{$item->id}/changes", [
                'type' => 'quantity_change',
                'quantity' => 2,
                'reason' => 'Customer minta tambah porsi',
            ]);

        $changeRes->assertStatus(201)
            ->assertJsonPath('data.status', 'pending_customer')
            ->assertJsonPath('message', 'Perubahan order berhasil dibuat.');

        $changeId = $changeRes->json('data.id');

        // 3. Customer approves change
        $approveRes = $this->actingAs($this->user)
            ->patchJson("/api/v1/order-item-changes/{$changeId}/approve");

        $approveRes->assertStatus(200)
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.new_quantity', 2);
    }

    public function test_domain_exception_returns_422(): void
    {
        // Try to create order on already occupied table
        $this->table->update(['status' => \App\Enums\TableStatus::Occupied]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/orders', [
                'order_table_id' => $this->table->id,
                'customer_name' => 'Testing Conflict',
                'guest_count' => 1,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Meja sedang digunakan.');
    }

    public function test_cashier_cannot_access_kitchen_endpoints(): void
    {
        $cashier = User::firstOrCreate(
            ['email' => 'cashier_test@seafood.com'],
            [
                'restaurant_id' => $this->user->restaurant_id,
                'name' => 'Cashier Test User',
                'status' => 'active',
            ]
        );
        $cashier->syncRoles(['cashier']);

        $response = $this->actingAs($cashier)
            ->getJson('/api/v1/kitchen/orders');

        $response->assertStatus(403)
            ->assertJsonPath('error', 'forbidden');
    }

    public function test_kitchen_cannot_create_orders(): void
    {
        $kitchenStaff = User::firstOrCreate(
            ['email' => 'kitchen_test@seafood.com'],
            [
                'restaurant_id' => $this->user->restaurant_id,
                'name' => 'Kitchen Test User',
                'status' => 'active',
            ]
        );
        $kitchenStaff->syncRoles(['kitchen']);

        $response = $this->actingAs($kitchenStaff)
            ->postJson('/api/v1/orders', [
                'order_table_id' => $this->table->id,
                'customer_name' => 'Illegal Order by Kitchen',
                'guest_count' => 1,
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('error', 'forbidden');
    }

    public function test_cashier_can_create_order_and_payment(): void
    {
        $this->table->update(['status' => \App\Enums\TableStatus::Available]);

        $cashier = User::firstOrCreate(
            ['email' => 'cashier_test@seafood.com'],
            [
                'restaurant_id' => $this->user->restaurant_id,
                'name' => 'Cashier Test User',
                'status' => 'active',
            ]
        );
        $cashier->syncRoles(['cashier']);

        // Cashier creates order
        $createOrderRes = $this->actingAs($cashier)
            ->postJson('/api/v1/orders', [
                'order_table_id' => $this->table->id,
                'customer_name' => 'Customer Kasir',
                'guest_count' => 2,
            ]);

        $createOrderRes->assertStatus(201);
    }

    public function test_fifo_costing_and_profit_calculation(): void
    {
        $this->table->update(['status' => \App\Enums\TableStatus::Available]);

        // 1. Stock In via InventoryService (2 batches FIFO)
        $inventoryService = app(\App\Services\InventoryService::class);
        $ingredient = $this->variant->mainIngredient;

        // Batch 1: 5 kg @ Rp 20,000
        $inventoryService->addStock([
            'outlet_id' => $this->outlet->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 5,
            'cost_per_unit' => 20000,
            'received_at' => now()->subHours(2),
        ]);

        // Batch 2: 5 kg @ Rp 30,000
        $inventoryService->addStock([
            'outlet_id' => $this->outlet->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 5,
            'cost_per_unit' => 30000,
            'received_at' => now()->subHour(1),
        ]);

        // 2. Create Order & Item with 7 units (conversion_rate = 0.6 -> 7 * 0.6 = 4.2 kg or quantity = 7 kg if 1.0)
        // Let's set conversion_rate to 1.0 for variant so 7 portions = 7.0 kg of ingredient
        $this->variant->update(['conversion_rate' => 1.0, 'price' => 50000]);

        $createOrderRes = $this->actingAs($this->user)
            ->postJson('/api/v1/orders', [
                'order_table_id' => $this->table->id,
                'customer_name' => 'Pak Haji FIFO',
                'guest_count' => 4,
            ]);
        $orderId = $createOrderRes->json('data.id');

        $this->actingAs($this->user)
            ->postJson("/api/v1/orders/{$orderId}/items", [
                'menu_variant_id' => $this->variant->id,
                'quantity' => 7, // 7 kg needed: 5 kg from Batch 1 (5*20k = 100k) + 2 kg from Batch 2 (2*30k = 60k) = 160k COGS
            ]);

        $order = Order::find($orderId);
        $batch = $order->batches()->first();
        $item = $order->items()->first();

        // Submit to kitchen & accept
        $this->actingAs($this->user)->postJson("/api/v1/order-batches/{$batch->id}/submit");
        $this->actingAs($this->user)->patchJson("/api/v1/kitchen/batches/{$batch->id}/accept");

        // 3. Kitchen prepare item -> triggers FIFO deduction & COGS calculation!
        $prepareRes = $this->actingAs($this->user)
            ->patchJson("/api/v1/kitchen/items/{$item->id}/prepare");

        $prepareRes->assertStatus(200);

        // Expected Subtotal: 7 * 50,000 = 350,000
        // Expected FIFO COGS: 5 * 20,000 + 2 * 30,000 = 160,000
        // Expected Gross Profit: 350,000 - 160,000 = 190,000
        $order->refresh();
        $item->refresh();

        $this->assertEquals(160000.0, (float) $item->cogs);
        $this->assertEquals(190000.0, (float) $item->gross_profit);
        $this->assertEquals(160000.0, (float) $order->total_cogs);
        $this->assertEquals(190000.0, (float) $order->gross_profit);

        // 4. Test Single Order Profit API endpoint
        $orderProfitRes = $this->actingAs($this->user)
            ->getJson("/api/v1/reports/orders/{$orderId}/profit");

        $orderProfitRes->assertStatus(200)
            ->assertJsonPath('data.revenue', 350000)
            ->assertJsonPath('data.total_cogs', 160000)
            ->assertJsonPath('data.gross_profit', 190000)
            ->assertJsonPath('data.margin_percentage', 54.29);

        // Complete the order to verify summary report
        $this->actingAs($this->user)->patchJson("/api/v1/kitchen/items/{$item->id}/ready");
        $this->actingAs($this->user)->patchJson("/api/v1/kitchen/items/{$item->id}/serve");
        $this->actingAs($this->user)->postJson("/api/v1/orders/{$orderId}/payments", [
            'method' => 'cash',
            'amount' => 350000,
        ]);

        // 5. Test Profit Summary API endpoint
        $summaryRes = $this->actingAs($this->user)
            ->getJson('/api/v1/reports/profit/summary');

        $summaryRes->assertStatus(200);
        $this->assertGreaterThanOrEqual(350000, (float) $summaryRes->json('data.total_revenue'));
        $this->assertGreaterThanOrEqual(160000, (float) $summaryRes->json('data.total_cogs'));
        $this->assertGreaterThanOrEqual(190000, (float) $summaryRes->json('data.total_gross_profit'));
    }

    public function test_cashier_cannot_access_profit_report(): void
    {
        $cashier = User::firstOrCreate(
            ['email' => 'cashier_test@seafood.com'],
            [
                'restaurant_id' => $this->user->restaurant_id,
                'name' => 'Cashier Test User',
                'status' => 'active',
            ]
        );
        $cashier->syncRoles(['cashier']);

        $response = $this->actingAs($cashier)
            ->getJson('/api/v1/reports/profit/summary');

        $response->assertStatus(403)
            ->assertJsonPath('error', 'forbidden');
    }
}
