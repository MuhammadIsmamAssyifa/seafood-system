<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
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
            ['code' => 'SEAFOOD-SYS'],
            [
                'name' => 'Resto Bahari Utama',
                'status' => 'active',
                'phone' => '0812-1111-2222',
                'address' => 'Jl. Bahari No. 10',
            ]
        );

        $this->outlet = Outlet::firstOrCreate(
            ['code' => 'OUT-SYS-01'],
            ['restaurant_id' => $this->restaurant->id, 'name' => 'Outlet Pusat', 'status' => 'active']
        );

        $this->owner = User::firstOrCreate(
            ['email' => 'owner_sys@seafood.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Owner Settings',
                'status' => 'active',
                'password' => bcrypt('password123'),
            ]
        );
        $this->owner->syncRoles(['owner']);
        $this->owner->outlets()->sync([$this->outlet->id]);

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_sys@seafood.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Admin Settings',
                'status' => 'active',
                'password' => bcrypt('password123'),
            ]
        );
        $this->admin->syncRoles(['admin']);
        $this->admin->outlets()->sync([$this->outlet->id]);

        $this->cashier = User::firstOrCreate(
            ['email' => 'cashier_sys@seafood.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Cashier Settings',
                'status' => 'active',
                'pin' => '2233',
            ]
        );
        $this->cashier->syncRoles(['cashier']);
        $this->cashier->outlets()->sync([$this->outlet->id]);
    }

    public function test_can_fetch_system_settings_with_defaults(): void
    {
        $response = $this->actingAs($this->owner, 'sanctum')->getJson('/api/v1/settings');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'restaurant' => ['name', 'address', 'phone', 'footer_message'],
                    'pos' => ['allow_discount', 'require_customer_name', 'default_payment'],
                    'printer' => [
                        'cashier' => ['enabled', 'printer_name'],
                        'kitchen' => ['enabled', 'printer_name'],
                    ],
                    'payment' => ['cash_enabled', 'transfer_enabled', 'qris_enabled'],
                    'inventory' => ['low_stock_threshold'],
                ],
            ]);
    }

    public function test_owner_can_update_settings_and_sync_restaurant_profile(): void
    {
        $payload = [
            'restaurant' => [
                'name' => 'Resto Bahari Megah',
                'address' => 'Jl. Samudera No. 99',
                'phone' => '0899-8888-7777',
                'footer_message' => 'Terima kasih atas kunjungan Anda!',
            ],
            'pos' => [
                'allow_discount' => false,
                'require_customer_name' => true,
                'default_payment' => 'transfer',
            ],
            'printer' => [
                'cashier' => [
                    'enabled' => true,
                    'printer_name' => 'EPSON TM-U220',
                ],
                'kitchen' => [
                    'enabled' => true,
                    'printer_name' => 'Star Micronics TSP100',
                ],
            ],
            'payment' => [
                'cash_enabled' => true,
                'transfer_enabled' => true,
                'qris_enabled' => false,
            ],
            'inventory' => [
                'low_stock_threshold' => 15,
            ],
        ];

        $response = $this->actingAs($this->owner, 'sanctum')->putJson('/api/v1/settings', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.restaurant.name', 'Resto Bahari Megah')
            ->assertJsonPath('data.pos.allow_discount', false)
            ->assertJsonPath('data.pos.default_payment', 'transfer')
            ->assertJsonPath('data.printer.cashier.printer_name', 'EPSON TM-U220')
            ->assertJsonPath('data.inventory.low_stock_threshold', 15);

        // Verify restaurant profile in database
        $this->assertDatabaseHas('restaurants', [
            'id' => $this->restaurant->id,
            'name' => 'Resto Bahari Megah',
            'phone' => '0899-8888-7777',
        ]);

        // Verify setting row in database
        $this->assertDatabaseHas('settings', [
            'restaurant_id' => $this->restaurant->id,
            'key' => 'pos.default_payment',
            'value' => 'transfer',
        ]);

        // Verify audit log creation
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'settings_updated',
            'module' => 'Settings',
        ]);
    }

    public function test_rejects_update_when_printer_name_is_empty_for_enabled_printer(): void
    {
        $payload = [
            'printer' => [
                'cashier' => [
                    'enabled' => true,
                    'printer_name' => '', // invalid: empty when enabled
                ],
            ],
        ];

        $response = $this->actingAs($this->owner, 'sanctum')->putJson('/api/v1/settings', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_rejects_disabling_all_payment_methods(): void
    {
        $payload = [
            'payment' => [
                'cash_enabled' => false,
                'transfer_enabled' => false,
                'qris_enabled' => false,
            ],
        ];

        $response = $this->actingAs($this->owner, 'sanctum')->putJson('/api/v1/settings', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_cashier_is_forbidden_from_viewing_or_updating_settings(): void
    {
        $getRes = $this->actingAs($this->cashier, 'sanctum')->getJson('/api/v1/settings');
        $getRes->assertStatus(403);

        $putRes = $this->actingAs($this->cashier, 'sanctum')->putJson('/api/v1/settings', [
            'pos' => ['allow_discount' => false],
        ]);
        $putRes->assertStatus(403);
    }
}

