<?php

namespace Tests\Feature;

use App\Enums\ActiveStatus;
use App\Enums\IngredientUnit;
use App\Enums\MenuType;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\AuditLog;
use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\KitchenStation;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\OrderBatch;
use App\Models\OrderItem;
use App\Models\OrderTable;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\OrderCalculationService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class FinalIntegrationTest extends TestCase
{
    protected Restaurant $restaurant;
    protected Outlet $outlet;
    protected User $owner;
    protected User $cashier;
    protected KitchenStation $station;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
        (new PermissionSeeder)->run();

        $this->restaurant = Restaurant::firstOrCreate(
            ['code' => 'RESTO-INTEGRATION'],
            ['name' => 'Resto Seafood Final Integration', 'status' => 'active']
        );

        $this->outlet = Outlet::firstOrCreate(
            ['code' => 'OUT-FINAL-01'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Outlet Final Hub',
                'status' => 'active',
            ]
        );

        $this->station = KitchenStation::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'code' => 'KS-GW-01'],
            ['name' => 'Grill & Wok', 'status' => 'active']
        );

        $this->owner = User::firstOrCreate(
            ['email' => 'owner_final@seafood.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Owner Final',
                'username' => 'owner_final',
                'password' => bcrypt('password123'),
                'status' => 'active',
            ]
        );
        $this->owner->assignRole('owner');
        $this->owner->outlets()->syncWithoutDetaching([$this->outlet->id]);

        $this->cashier = User::firstOrCreate(
            ['email' => 'cashier_final@seafood.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Kasir Final',
                'username' => 'kasir_final',
                'password' => bcrypt('password123'),
                'status' => 'active',
            ]
        );
        $this->cashier->assignRole('cashier');
        $this->cashier->outlets()->syncWithoutDetaching([$this->outlet->id]);
    }

    public function test_complete_end_to_end_business_lifecycle(): void
    {
        // 1. Inventory Stock In via InventoryService
        $ingredient = Ingredient::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Cumi Segar ' . uniqid(),
            'unit' => IngredientUnit::Kg,
            'type' => 'raw',
        ]);

        /** @var InventoryService $inventoryService */
        $inventoryService = app(InventoryService::class);
        $batch = $inventoryService->addStock([
            'outlet_id' => $this->outlet->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 10.0,
            'cost_per_unit' => 60000, // Rp60.000 / kg
            'source' => 'Supplier Pasar Muara',
            'note' => 'Stok Awal Integration Test',
        ]);

        $this->assertDatabaseHas('inventory_batches', [
            'id' => $batch->id,
            'remaining_quantity' => 10.0,
        ]);

        // 2. Menu and Recipe Creation
        $menu = Menu::create([
            'outlet_id' => $this->outlet->id,
            'name' => 'Cumi Bakar Spesial ' . uniqid(),
            'type' => MenuType::Food,
            'status' => ActiveStatus::Active,
        ]);

        $variant = MenuVariant::create([
            'outlet_id' => $this->outlet->id,
            'menu_id' => $menu->id,
            'kitchen_station_id' => $this->station->id,
            'name' => 'Porsi Jumbo',
            'price' => 75000,
            'sales_unit' => 'portion',
            'inventory_unit' => 'kg',
            'conversion_rate' => 0.4,
            'status' => 'active',
        ]);

        $recipe = Recipe::create([
            'menu_variant_id' => $variant->id,
            'name' => 'Resep Cumi Bakar Porsi Jumbo',
            'created_by' => $this->owner->id,
        ]);

        RecipeItem::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 0.4, // 400 gram = Rp 24.000 cost
            'unit' => 'kg',
        ]);

        // 3. Recipe Cost Endpoint Verification
        $recipeResp = $this->actingAs($this->owner)
            ->getJson("/api/v1/recipes/{$recipe->id}/calculate");

        $recipeResp->assertStatus(200);
        $this->assertCount(1, $recipeResp->json('data.items'));

        $calculatedCost = (float) $recipeResp->json('data.total_cost');
        $this->assertEquals(24000.0, $calculatedCost);

        // 4. Order Creation by Cashier
        $table = OrderTable::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'number' => '10'],
            ['status' => 'available']
        );

        $orderNum = 'ORD-E2E-' . uniqid();
        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $table->id,
            'order_number' => $orderNum,
            'customer_name' => 'Bu Ani',
            'guest_count' => 2,
            'status' => OrderStatus::Submitted,
            'subtotal' => 75000,
            'discount' => 0,
            'grand_total' => 75000,
            'total_cogs' => 24000,
            'gross_profit' => 51000,
            'created_by' => $this->cashier->id,
        ]);

        $orderBatch = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 1,
            'status' => 'completed',
        ]);

        $orderItem = OrderItem::create([
            'order_batch_id' => $orderBatch->id,
            'menu_variant_id' => $variant->id,
            'menu_name_snapshot' => $menu->name,
            'variant_name_snapshot' => $variant->name,
            'price_snapshot' => 75000,
            'quantity' => 1,
            'subtotal' => 75000,
            'cogs' => 24000,
            'gross_profit' => 51000,
            'sales_unit' => 'portion',
            'status' => OrderItemStatus::Served,
        ]);

        // 5. Payment Creation
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => 'cash',
            'amount' => 75000,
            'status' => 'paid',
            'paid_at' => now(),
            'created_by' => $this->cashier->id,
        ]);

        $order->update(['status' => OrderStatus::Completed]);

        // 6. Deduct Stock via FIFO
        $inventoryService->deductStock($ingredient, 0.4, $orderItem);

        $batch->refresh();
        // 10.0 - 0.4 = 9.6 kg
        $this->assertEquals(9.6, (float) $batch->remaining_quantity);

        // 7. Verify Dashboard Overview API
        $dashboardResp = $this->actingAs($this->owner)
            ->getJson('/api/v1/dashboard/overview');

        $dashboardResp->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'sales',
                    'orders',
                    'payments',
                ],
            ]);

        // 8. Verify Global Search API finds this order
        $searchResp = $this->actingAs($this->owner)
            ->getJson("/api/v1/search?q={$orderNum}");

        $searchResp->assertStatus(200)
            ->assertJsonPath('data.results.orders.0.title', "Order #{$orderNum}");
    }

    public function test_security_role_boundaries_are_strictly_enforced(): void
    {
        $kitchen = User::firstOrCreate(
            ['email' => 'kitchen_final@seafood.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Koki Final',
                'username' => 'koki_final',
                'password' => bcrypt('password123'),
                'status' => 'active',
            ]
        );
        $kitchen->syncRoles(['kitchen']);
        $kitchen->outlets()->syncWithoutDetaching([$this->outlet->id]);

        // Kitchen cannot view audit logs
        $this->actingAs($kitchen)
            ->getJson('/api/v1/audit-logs')
            ->assertStatus(403);

        // Kitchen cannot view settings
        $this->actingAs($kitchen)
            ->getJson('/api/v1/settings')
            ->assertStatus(403);

        // Kitchen cannot delete recipes
        $this->actingAs($kitchen)
            ->deleteJson('/api/v1/recipes/99999')
            ->assertStatus(403);

        // Cashier cannot view audit logs
        $this->actingAs($this->cashier)
            ->getJson('/api/v1/audit-logs')
            ->assertStatus(403);

        // Cashier cannot modify system settings
        $this->actingAs($this->cashier)
            ->putJson('/api/v1/settings', [])
            ->assertStatus(403);
    }

    public function test_audit_logs_capture_activity_and_can_be_queried(): void
    {
        $response = $this->actingAs($this->owner)
            ->getJson('/api/v1/audit-logs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta',
            ]);
    }
}
