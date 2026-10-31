<?php

namespace Tests\Feature;

use App\Enums\IngredientUnit;
use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\KitchenStation;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Outlet;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class RecipeManagementTest extends TestCase
{
    protected User $owner;
    protected User $admin;
    protected User $cashier;
    protected Outlet $outlet;
    protected MenuVariant $variant;
    protected Ingredient $ingredientCumi;
    protected Ingredient $ingredientBumbu;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
        (new PermissionSeeder)->run();

        $restaurant = Restaurant::firstOrCreate(
            ['code' => 'SEAFOOD-RECIPE'],
            ['name' => 'Resto Seafood Recipe', 'status' => 'active']
        );

        $this->outlet = Outlet::firstOrCreate(
            ['code' => 'OUT-RCP-01'],
            ['restaurant_id' => $restaurant->id, 'name' => 'Outlet Recipe', 'status' => 'active']
        );

        $this->owner = User::firstOrCreate(
            ['email' => 'owner_rcp@seafood.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Owner Recipe',
                'status' => 'active',
                'password' => bcrypt('password123'),
            ]
        );
        $this->owner->syncRoles(['owner']);
        $this->owner->outlets()->sync([$this->outlet->id]);

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_rcp@seafood.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Admin Recipe',
                'status' => 'active',
                'password' => bcrypt('password123'),
            ]
        );
        $this->admin->syncRoles(['admin']);
        $this->admin->outlets()->sync([$this->outlet->id]);

        $this->cashier = User::firstOrCreate(
            ['email' => 'cashier_rcp@seafood.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Cashier Recipe',
                'status' => 'active',
                'pin' => '3344',
            ]
        );
        $this->cashier->syncRoles(['cashier']);
        $this->cashier->outlets()->sync([$this->outlet->id]);

        $menu = Menu::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Cumi Asam Manis'],
            [
                'type' => 'seafood',
                'status' => 'active',
            ]
        );

        $station = KitchenStation::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'code' => 'ST-WOK-RCP'],
            ['name' => 'Wok Station RCP', 'status' => 'active']
        );

        $this->variant = MenuVariant::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Porsi Reguler RCP'],
            [
                'menu_id' => $menu->id,
                'kitchen_station_id' => $station->id,
                'price' => 100000.00,
                'sales_unit' => 'portion',
                'inventory_unit' => 'kg',
                'conversion_rate' => 0.5,
                'status' => 'active',
            ]
        );

        $this->ingredientCumi = Ingredient::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Cumi Segar RCP'],
            ['unit' => 'kg', 'status' => 'active', 'type' => 'raw_material']
        );

        $this->ingredientBumbu = Ingredient::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Bumbu Saus RCP'],
            ['unit' => 'kg', 'status' => 'active', 'type' => 'raw_material']
        );

        // Batches for pricing
        InventoryBatch::create([
            'outlet_id' => $this->outlet->id,
            'ingredient_id' => $this->ingredientCumi->id,
            'quantity' => 10,
            'remaining_quantity' => 10,
            'cost_per_unit' => 80000.00, // 80k/kg
            'received_at' => now(),
        ]);

        InventoryBatch::create([
            'outlet_id' => $this->outlet->id,
            'ingredient_id' => $this->ingredientBumbu->id,
            'quantity' => 5,
            'remaining_quantity' => 5,
            'cost_per_unit' => 20000.00, // 20k/kg
            'received_at' => now(),
        ]);

        // Clean previous recipe for clean test isolation
        RecipeItem::whereHas('recipe', fn ($q) => $q->where('menu_variant_id', $this->variant->id))->delete();
        Recipe::where('menu_variant_id', $this->variant->id)->delete();
    }

    public function test_owner_can_create_recipe_with_cost_calculation(): void
    {
        $payload = [
            'menu_variant_id' => $this->variant->id,
            'name' => 'Resep Cumi Asam Manis Mantap',
            'description' => 'Takaran bumbu resep rahasia koki',
            'items' => [
                [
                    'ingredient_id' => $this->ingredientCumi->id,
                    'quantity' => 0.5,
                    'unit' => 'kg',
                ],
                [
                    'ingredient_id' => $this->ingredientBumbu->id,
                    'quantity' => 0.1,
                    'unit' => 'kg',
                ],
            ],
        ];

        $response = $this->actingAs($this->owner, 'sanctum')->postJson('/api/v1/recipes', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Resep Cumi Asam Manis Mantap')
            ->assertJsonPath('data.items_count', 2);

        $this->assertDatabaseHas('recipes', [
            'menu_variant_id' => $this->variant->id,
            'name' => 'Resep Cumi Asam Manis Mantap',
        ]);

        $this->assertDatabaseHas('recipe_items', [
            'ingredient_id' => $this->ingredientCumi->id,
            'quantity' => 0.5,
        ]);

        // Audit log verified
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'recipe_created',
            'module' => 'Recipe',
        ]);
    }

    public function test_cannot_create_duplicate_recipe_for_same_variant(): void
    {
        Recipe::create([
            'menu_variant_id' => $this->variant->id,
            'name' => 'Resep Awal',
        ]);

        $payload = [
            'menu_variant_id' => $this->variant->id,
            'items' => [
                [
                    'ingredient_id' => $this->ingredientCumi->id,
                    'quantity' => 0.5,
                    'unit' => 'kg',
                ],
            ],
        ];

        $response = $this->actingAs($this->owner, 'sanctum')->postJson('/api/v1/recipes', $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors('menu_variant_id');
    }

    public function test_can_fetch_recipes_list_with_cost_and_food_cost_percentage(): void
    {
        $recipe = Recipe::create([
            'menu_variant_id' => $this->variant->id,
            'name' => 'Resep Test List',
        ]);

        // 0.5 kg Cumi (40,000) + 0.1 kg Bumbu (2,000) = 42,000 total cost
        // Selling price = 100,000 -> Food cost % = 42.0%
        $recipe->items()->create([
            'ingredient_id' => $this->ingredientCumi->id,
            'quantity' => 0.5,
            'unit' => 'kg',
        ]);
        $recipe->items()->create([
            'ingredient_id' => $this->ingredientBumbu->id,
            'quantity' => 0.1,
            'unit' => 'kg',
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')->getJson('/api/v1/recipes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'menu_variant',
                        'items_count',
                        'cost_calculation' => [
                            'total_cost',
                            'selling_price',
                            'gross_margin',
                            'food_cost_percentage',
                            'margin_percentage',
                        ],
                    ],
                ],
            ]);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_owner_can_update_recipe_items(): void
    {
        $recipe = Recipe::create([
            'menu_variant_id' => $this->variant->id,
            'name' => 'Resep Sebelum Update',
        ]);

        $recipe->items()->create([
            'ingredient_id' => $this->ingredientCumi->id,
            'quantity' => 0.5,
            'unit' => 'kg',
        ]);

        $payload = [
            'name' => 'Resep Sesudah Update',
            'items' => [
                [
                    'ingredient_id' => $this->ingredientCumi->id,
                    'quantity' => 0.6, // update quantity
                    'unit' => 'kg',
                ],
            ],
        ];

        $response = $this->actingAs($this->owner, 'sanctum')->putJson("/api/v1/recipes/{$recipe->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Resep Sesudah Update');

        $this->assertDatabaseHas('recipe_items', [
            'recipe_id' => $recipe->id,
            'quantity' => 0.6,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'recipe_updated',
            'module' => 'Recipe',
        ]);
    }

    public function test_owner_can_delete_recipe(): void
    {
        $recipe = Recipe::create([
            'menu_variant_id' => $this->variant->id,
            'name' => 'Resep Hapus',
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')->deleteJson("/api/v1/recipes/{$recipe->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
    }

    public function test_cashier_is_forbidden_from_viewing_or_managing_recipes(): void
    {
        $getRes = $this->actingAs($this->cashier, 'sanctum')->getJson('/api/v1/recipes');
        $getRes->assertStatus(403);

        $postRes = $this->actingAs($this->cashier, 'sanctum')->postJson('/api/v1/recipes', [
            'menu_variant_id' => $this->variant->id,
            'items' => [],
        ]);
        $postRes->assertStatus(403);
    }
}
