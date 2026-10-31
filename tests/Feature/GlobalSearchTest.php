<?php

namespace Tests\Feature;

use App\Enums\IngredientUnit;
use App\Enums\OrderStatus;
use App\Models\Ingredient;
use App\Models\KitchenStation;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\OrderTable;
use App\Models\Outlet;
use App\Models\Recipe;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    protected User $owner;
    protected Outlet $outlet;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
        (new PermissionSeeder)->run();

        $restaurant = Restaurant::firstOrCreate(
            ['code' => 'SEAFOOD-SRCH'],
            ['name' => 'Resto Seafood Search', 'status' => 'active']
        );

        $this->outlet = Outlet::firstOrCreate(
            ['code' => 'OUT-SRCH-01'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Outlet Search Hub',
                'status' => 'active',
            ]
        );

        $this->owner = User::firstOrCreate(
            ['email' => 'owner_search@seafood.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Owner Search Test',
                'username' => 'owner_search',
                'password' => bcrypt('password123'),
                'status' => 'active',
            ]
        );
        $this->owner->assignRole('owner');
        $this->owner->outlets()->syncWithoutDetaching([$this->outlet->id]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/search?q=test');
        $response->assertStatus(401);
    }

    public function test_short_query_returns_empty_results(): void
    {
        $response = $this->actingAs($this->owner)
            ->getJson('/api/v1/search?q=a');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'query' => 'a',
                    'total_results' => 0,
                    'results' => [
                        'orders' => [],
                        'menus' => [],
                        'inventory' => [],
                        'employees' => [],
                        'recipes' => [],
                        'outlets' => [],
                    ],
                ],
            ]);
    }

    public function test_search_matches_order(): void
    {
        $table = OrderTable::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'number' => '99'],
            ['status' => 'available']
        );

        $orderNum = 'ORD-SEARCH-' . uniqid();
        Order::create([
            'outlet_id' => $this->outlet->id,
            'order_table_id' => $table->id,
            'order_number' => $orderNum,
            'customer_name' => 'Pak Joko Search',
            'status' => OrderStatus::Submitted,
            'subtotal' => 150000,
            'grand_total' => 150000,
            'created_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->getJson("/api/v1/search?q={$orderNum}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.results.orders.0.title', "Order #{$orderNum}")
            ->assertJsonPath('data.results.orders.0.type', 'order');
    }

    public function test_search_matches_menu_and_ingredient(): void
    {
        $menuName = 'Kepiting Saus Padang ' . uniqid();
        $menu = Menu::create([
            'outlet_id' => $this->outlet->id,
            'name' => $menuName,
            'type' => 'food',
        ]);

        $ingName = 'Bumbu Saus Padang ' . uniqid();
        $ingredient = Ingredient::create([
            'outlet_id' => $this->outlet->id,
            'name' => $ingName,
            'unit' => IngredientUnit::Kg,
            'type' => 'raw',
        ]);

        $response = $this->actingAs($this->owner)
            ->getJson("/api/v1/search?q={$menuName}");

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data.results.menus'));
        $this->assertEquals($menuName, $response->json('data.results.menus.0.title'));

        $responseIng = $this->actingAs($this->owner)
            ->getJson("/api/v1/search?q={$ingName}");

        $responseIng->assertStatus(200);
        $this->assertNotEmpty($responseIng->json('data.results.inventory'));
        $this->assertEquals($ingName, $responseIng->json('data.results.inventory.0.title'));
    }

    public function test_search_matches_employee_and_outlet(): void
    {
        $responseUser = $this->actingAs($this->owner)
            ->getJson('/api/v1/search?q=Owner Search Test');

        $responseUser->assertStatus(200);
        $this->assertNotEmpty($responseUser->json('data.results.employees'));
        $this->assertEquals('Owner Search Test', $responseUser->json('data.results.employees.0.title'));

        $responseOutlet = $this->actingAs($this->owner)
            ->getJson('/api/v1/search?q=Outlet Search Hub');

        $responseOutlet->assertStatus(200);
        $this->assertNotEmpty($responseOutlet->json('data.results.outlets'));
        $this->assertEquals('Outlet Search Hub', $responseOutlet->json('data.results.outlets.0.title'));
    }
}
