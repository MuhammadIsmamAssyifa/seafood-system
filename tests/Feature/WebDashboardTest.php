<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class WebDashboardTest extends TestCase
{
    protected User $owner;

    protected Outlet $outlet;

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
            ['email' => 'owner_web@seafood.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Owner Web',
                'username' => 'owner_web',
                'status' => 'active',
                'password' => bcrypt('password123'),
            ]
        );
        $this->owner->syncRoles(['owner']);
        $this->owner->outlets()->sync([$this->outlet->id]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200)
            ->assertSee('Seafood RMS')
            ->assertSee('Masuk ke Portal');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'login' => 'owner_web@seafood.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticatedAs($this->owner);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'login' => 'owner_web@seafood.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $response = $this->actingAs($this->owner)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_dashboard_index_renders_successfully(): void
    {
        $response = $this->actingAs($this->owner)->get('/dashboard');

        $response->assertStatus(200)
            ->assertSee('Ringkasan Restoran')
            ->assertSee('Omzet Hari Ini')
            ->assertSee('HPP (COGS FIFO)');
    }

    public function test_reports_page_renders_with_tabs(): void
    {
        $response = $this->actingAs($this->owner)->get('/reports?tab=sales');

        $response->assertStatus(200)
            ->assertSee('Laporan & Analitik')
            ->assertSee('Ringkasan Penjualan');

        $profitRes = $this->actingAs($this->owner)->get('/reports?tab=profit');
        $profitRes->assertStatus(200)
            ->assertSee('Breakdown Profit per Menu Seafood');
    }

    public function test_inventory_page_renders_and_allows_restocking(): void
    {
        $ingredient = Ingredient::firstOrCreate(
            ['outlet_id' => $this->outlet->id, 'name' => 'Kepiting Tarakan Web'],
            ['unit' => 'kg', 'type' => 'raw', 'status' => 'active']
        );

        $response = $this->actingAs($this->owner)->get('/inventory');
        $response->assertStatus(200)
            ->assertSee('Inventori & Stok Bahan Baku');

        $postRes = $this->actingAs($this->owner)->post('/inventory/stock', [
            'ingredient_id' => $ingredient->id,
            'quantity' => 15,
            'cost_per_unit' => 115000,
            'note' => 'Restock via Web Dashboard',
        ]);

        $postRes->assertRedirect('/inventory');
        $postRes->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_batches', [
            'ingredient_id' => $ingredient->id,
            'quantity' => 15,
            'cost_per_unit' => 115000,
        ]);
    }

    public function test_menus_page_renders_catalog(): void
    {
        $response = $this->actingAs($this->owner)->get('/menus');

        $response->assertStatus(200)
            ->assertSee('Katalog Menu & Varian Harga');
    }

    public function test_users_page_renders_and_allows_creating_user(): void
    {
        $response = $this->actingAs($this->owner)->get('/users');
        $response->assertStatus(200)
            ->assertSee('Daftar Akun Pengguna');

        $unique = uniqid();
        $email = "staf_{$unique}@seafood.com";
        $username = "staf_{$unique}";

        $postRes = $this->actingAs($this->owner)->post('/users', [
            'name' => 'Staf Baru Kasir',
            'email' => $email,
            'username' => $username,
            'password' => 'secret123',
            'pin' => '4321',
            'role' => 'cashier',
        ]);

        $postRes->assertRedirect('/users');
        $postRes->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'username' => $username,
            'email' => $email,
        ]);
    }

    public function test_audit_logs_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->owner)->get('/audit-logs');

        $response->assertStatus(200)
            ->assertSee('Riwayat Jejak Audit Sistem');
    }
}
