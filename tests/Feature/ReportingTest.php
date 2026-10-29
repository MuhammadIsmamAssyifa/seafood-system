<?php

namespace Tests\Feature;

use App\Enums\TableStatus;
use App\Models\CookingMethod;
use App\Models\Ingredient;
use App\Models\KitchenStation;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\OrderTable;
use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class ReportingTest extends TestCase
{
    protected User $owner;

    protected User $cashier;

    protected Outlet $outlet;

    protected OrderTable $table;

    protected MenuVariant $variant;

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
            ['email' => 'owner_report@seafood.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Owner Report',
                'status' => 'active',
            ]
        );
        $this->owner->syncRoles(['owner']);

        $this->cashier = User::firstOrCreate(
            ['email' => 'cashier_report@seafood.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Cashier Report',
                'status' => 'active',
            ]
        );
        $this->cashier->syncRoles(['cashier']);

        if (! $this->owner->outlets()->where('outlets.id', $this->outlet->id)->exists()) {
            $this->owner->outlets()->attach($this->outlet->id);
        }
        if (! $this->cashier->outlets()->where('outlets.id', $this->outlet->id)->exists()) {
            $this->cashier->outlets()->attach($this->outlet->id);
        }

        $this->table = OrderTable::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'number' => 'T-02'],
            ['status' => 'available']
        );
        $this->table->update(['status' => TableStatus::Available]);

        $station = KitchenStation::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'code' => 'MASAK'],
            ['name' => 'Station Masak', 'status' => 'active']
        );

        $cooking = CookingMethod::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Goreng Tepung'],
            ['status' => 'active']
        );

        $ingredient = Ingredient::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Udang Windu'],
            ['unit' => 'kg', 'status' => 'active']
        );

        $menu = Menu::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Udang Goreng'],
            ['type' => 'seafood', 'status' => 'active']
        );

        $this->variant = MenuVariant::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Udang Goreng Porsi'],
            [
                'menu_id' => $menu->id,
                'cooking_method_id' => $cooking->id,
                'main_ingredient_id' => $ingredient->id,
                'kitchen_station_id' => $station->id,
                'price' => 60000,
                'sales_unit' => 'portion',
                'inventory_unit' => 'kg',
                'conversion_rate' => 0.5,
                'status' => 'active',
            ]
        );
    }

    public function test_dashboard_summary(): void
    {
        $response = $this->actingAs($this->owner)
            ->getJson('/api/v1/dashboard/summary');

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
                    ],
                    'orders' => [
                        'total_today',
                        'completed_today',
                        'active_orders',
                        'guests_today',
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
                ],
            ]);
    }

    public function test_sales_report(): void
    {
        $response = $this->actingAs($this->owner)
            ->getJson('/api/v1/reports/sales?start_date='.now()->subDays(7)->toDateString().'&end_date='.now()->toDateString());

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
                        'average_order_value',
                        'average_spend_per_guest',
                    ],
                    'daily_trends',
                ],
            ]);
    }

    public function test_payment_report(): void
    {
        $response = $this->actingAs($this->owner)
            ->getJson('/api/v1/reports/payments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'outlet_id',
                    'start_date',
                    'end_date',
                    'summary' => [
                        'total_collected',
                        'total_transactions',
                    ],
                    'method_breakdown',
                    'transactions',
                ],
            ]);
    }

    public function test_menu_sales_report(): void
    {
        $response = $this->actingAs($this->owner)
            ->getJson('/api/v1/reports/menu-sales');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'outlet_id',
                    'start_date',
                    'end_date',
                    'summary' => [
                        'total_items_sold',
                        'total_revenue',
                    ],
                    'menu_rankings',
                ],
            ]);
    }

    public function test_inventory_and_stock_reports(): void
    {
        // 1. Inventory Report
        $inventoryRes = $this->actingAs($this->owner)
            ->getJson('/api/v1/reports/inventory');

        $inventoryRes->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'outlet_id',
                    'summary' => [
                        'total_ingredients',
                        'total_valuation',
                        'low_stock_count',
                    ],
                    'ingredients',
                ],
            ]);

        // 2. Stock Movements Report
        $movementsRes = $this->actingAs($this->owner)
            ->getJson('/api/v1/reports/stock-movements');

        $movementsRes->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'outlet_id',
                    'summary' => [
                        'total_movements',
                        'total_in',
                        'total_sale',
                        'total_waste',
                        'total_adjustment',
                    ],
                    'movements',
                ],
            ]);
    }

    public function test_cashier_can_access_dashboard_but_cannot_access_reports(): void
    {
        // Cashier has dashboard.view permission
        $dashRes = $this->actingAs($this->cashier)
            ->getJson('/api/v1/dashboard/summary');
        $dashRes->assertStatus(200);

        // Cashier does NOT have report.view permission
        $salesRes = $this->actingAs($this->cashier)
            ->getJson('/api/v1/reports/sales');
        $salesRes->assertStatus(403)
            ->assertJsonPath('error', 'forbidden');
    }
}
