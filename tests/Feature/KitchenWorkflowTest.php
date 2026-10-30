<?php

namespace Tests\Feature;

use App\Enums\OrderBatchStatus;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
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
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\InventoryService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KitchenWorkflowTest extends TestCase
{
    protected Restaurant $restaurant;
    protected Outlet $outlet;
    protected User $kitchenUser;
    protected User $cashierUser;
    protected OrderTable $table;
    protected MenuVariant $variant;
    protected Ingredient $ingredient;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
        (new PermissionSeeder)->run();

        $this->restaurant = Restaurant::firstOrCreate(
            ['code' => 'SEAFOOD-RESTO-WF'],
            ['name' => 'Seafood Resto Workflow', 'status' => 'active']
        );

        $this->outlet = Outlet::firstOrCreate(
            ['code' => 'OUTLET-WF-01'],
            ['restaurant_id' => $this->restaurant->id, 'name' => 'Workflow Outlet', 'status' => 'active']
        );

        $this->kitchenUser = User::firstOrCreate(
            ['username' => 'chef_workflow'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Chef Workflow',
                'email' => 'chef.wf@example.com',
                'password' => Hash::make('password123'),
                'pin_hash' => Hash::make('123456'),
                'status' => 'active',
            ]
        );
        $this->kitchenUser->syncRoles(['kitchen']);
        $this->kitchenUser->outlets()->sync([$this->outlet->id]);

        $this->cashierUser = User::firstOrCreate(
            ['username' => 'cashier_wf'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Cashier Workflow',
                'email' => 'cashier.wf@example.com',
                'password' => Hash::make('password123'),
                'pin_hash' => Hash::make('1234'),
                'status' => 'active',
            ]
        );
        $this->cashierUser->syncRoles(['cashier']);
        $this->cashierUser->outlets()->sync([$this->outlet->id]);

        $this->table = OrderTable::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'number' => 'WF-01'],
            ['status' => 'available']
        );

        $station = KitchenStation::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'code' => 'GRILL-WF'],
            ['name' => 'Grill Station', 'status' => 'active']
        );

        $cooking = CookingMethod::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Bakar Madu'],
            ['status' => 'active']
        );

        $this->ingredient = Ingredient::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Ikan Gurame WF'],
            ['unit' => 'kg', 'status' => 'active']
        );

        // Add inventory stock for recipe deductions
        app(InventoryService::class)->addStock([
            'outlet_id' => $this->outlet->id,
            'ingredient_id' => $this->ingredient->id,
            'quantity' => 100.0,
            'cost_per_unit' => 30000,
        ]);

        $menu = Menu::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Gurame Bakar Madu'],
            ['type' => 'seafood', 'status' => 'active']
        );

        $this->variant = MenuVariant::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Gurame Porsi WF'],
            [
                'menu_id' => $menu->id,
                'kitchen_station_id' => $station->id,
                'cooking_method_id' => $cooking->id,
                'main_ingredient_id' => $this->ingredient->id,
                'price' => 75000,
                'sales_unit' => 'portion',
                'conversion_rate' => 0.5,
                'status' => 'active',
            ]
        );

        $recipe = Recipe::firstOrCreate(
            ['menu_variant_id' => $this->variant->id]
        );

        RecipeItem::firstOrCreate(
            [
                'recipe_id' => $recipe->id,
                'ingredient_id' => $this->ingredient->id,
            ],
            [
                'quantity' => 0.5,
                'unit' => 'kg',
            ]
        );
    }

    public function test_kitchen_can_accept_batch(): void
    {
        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table->id,
            'order_number' => 'ORD-WF-'.uniqid(),
            'customer_name' => 'Pak Andi',
            'status' => OrderStatus::Submitted,
            'created_by' => $this->kitchenUser->id,
        ]);

        $batch = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Sent,
        ]);

        $token = $this->kitchenUser->createToken('kds-test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/kitchen/batches/{$batch->id}/accept");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cooking');

        $this->assertEquals(OrderBatchStatus::Cooking, $batch->fresh()->status);
    }

    public function test_kitchen_can_prepare_item(): void
    {
        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table->id,
            'order_number' => 'ORD-WF-'.uniqid(),
            'customer_name' => 'Pak Budi',
            'status' => OrderStatus::Submitted,
            'created_by' => $this->kitchenUser->id,
        ]);

        $batch = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Cooking,
        ]);

        $item = OrderItem::create([
            'order_batch_id' => $batch->id,
            'menu_variant_id' => $this->variant->id,
            'menu_name_snapshot' => 'Gurame Bakar Madu',
            'quantity' => 2,
            'sales_unit' => 'portion',
            'price_snapshot' => 75000,
            'subtotal' => 150000,
            'status' => OrderItemStatus::Waiting,
        ]);

        $token = $this->kitchenUser->createToken('kds-test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/kitchen/items/{$item->id}/prepare");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'preparing');

        $this->assertEquals(OrderItemStatus::Preparing, $item->fresh()->status);
    }

    public function test_kitchen_can_ready_item(): void
    {
        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table->id,
            'order_number' => 'ORD-WF-'.uniqid(),
            'customer_name' => 'Pak Citra',
            'status' => OrderStatus::Preparing,
            'created_by' => $this->kitchenUser->id,
        ]);

        $batch = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Cooking,
        ]);

        $item = OrderItem::create([
            'order_batch_id' => $batch->id,
            'menu_variant_id' => $this->variant->id,
            'menu_name_snapshot' => 'Gurame Bakar Madu',
            'quantity' => 1,
            'sales_unit' => 'portion',
            'price_snapshot' => 75000,
            'subtotal' => 75000,
            'status' => OrderItemStatus::Preparing,
        ]);

        $token = $this->kitchenUser->createToken('kds-test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/kitchen/items/{$item->id}/ready");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'ready');

        $this->assertEquals(OrderItemStatus::Ready, $item->fresh()->status);
    }

    public function test_kitchen_can_serve_item(): void
    {
        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table->id,
            'order_number' => 'ORD-WF-'.uniqid(),
            'customer_name' => 'Pak Dedi',
            'status' => OrderStatus::Ready,
            'created_by' => $this->kitchenUser->id,
        ]);

        $batch = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Ready,
        ]);

        $item = OrderItem::create([
            'order_batch_id' => $batch->id,
            'menu_variant_id' => $this->variant->id,
            'menu_name_snapshot' => 'Gurame Bakar Madu',
            'quantity' => 1,
            'sales_unit' => 'portion',
            'price_snapshot' => 75000,
            'subtotal' => 75000,
            'status' => OrderItemStatus::Ready,
        ]);

        $token = $this->kitchenUser->createToken('kds-test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/kitchen/items/{$item->id}/serve");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'served');

        $this->assertEquals(OrderItemStatus::Served, $item->fresh()->status);
    }

    public function test_cannot_perform_invalid_status_transition(): void
    {
        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table->id,
            'order_number' => 'ORD-WF-'.uniqid(),
            'customer_name' => 'Pak Eko',
            'status' => OrderStatus::Completed,
            'created_by' => $this->kitchenUser->id,
        ]);

        $batch = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Completed,
        ]);

        $item = OrderItem::create([
            'order_batch_id' => $batch->id,
            'menu_variant_id' => $this->variant->id,
            'menu_name_snapshot' => 'Gurame Bakar Madu',
            'quantity' => 1,
            'sales_unit' => 'portion',
            'price_snapshot' => 75000,
            'subtotal' => 75000,
            'status' => OrderItemStatus::Served,
        ]);

        $token = $this->kitchenUser->createToken('kds-test')->plainTextToken;

        // Try preparing an already served item -> 422 DomainException
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/kitchen/items/{$item->id}/prepare");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Item tidak dapat diproses.');
    }

    public function test_unauthorized_user_cannot_update_kitchen_workflow(): void
    {
        $order = Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $this->table->id,
            'order_number' => 'ORD-WF-'.uniqid(),
            'customer_name' => 'Pak Fajar',
            'status' => OrderStatus::Submitted,
            'created_by' => $this->kitchenUser->id,
        ]);

        $batch = OrderBatch::create([
            'order_id' => $order->id,
            'batch_number' => 1,
            'status' => OrderBatchStatus::Sent,
        ]);

        $token = $this->cashierUser->createToken('cashier-test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/kitchen/batches/{$batch->id}/accept");

        $response->assertStatus(403);
    }
}
