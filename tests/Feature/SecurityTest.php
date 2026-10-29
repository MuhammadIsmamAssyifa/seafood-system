<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    protected User $owner;

    protected User $kitchen;

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
            ['email' => 'owner_security@seafood.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Owner Security',
                'status' => 'active',
                'password' => bcrypt('secret123'),
            ]
        );
        $this->owner->syncRoles(['owner']);
        $this->owner->outlets()->sync([$this->outlet->id]);

        $this->kitchen = User::firstOrCreate(
            ['email' => 'kitchen_security@seafood.com'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Kitchen Security',
                'status' => 'active',
                'password' => bcrypt('secret123'),
            ]
        );
        $this->kitchen->syncRoles(['kitchen']);
        $this->kitchen->outlets()->sync([$this->outlet->id]);
    }

    public function test_login_with_device_name_creates_named_token(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => $this->owner->email,
            'password' => 'secret123',
            'device_name' => 'POS-Tablet-01',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);

        $tokenModel = $this->owner->tokens()->latest()->first();
        $this->assertNotNull($tokenModel);
        $this->assertEquals('POS-Tablet-01', $tokenModel->name);
    }

    public function test_logout_revokes_current_access_token(): void
    {
        $token = $this->owner->createToken('test-device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);

        $this->assertCount(0, $this->owner->tokens()->where('name', 'test-device')->get());
    }

    public function test_logout_all_revokes_all_user_tokens(): void
    {
        $this->owner->createToken('device-1');
        $this->owner->createToken('device-2');
        $this->owner->createToken('device-3');

        $this->assertGreaterThanOrEqual(3, $this->owner->tokens()->count());

        $token = $this->owner->createToken('device-active')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/logout-all');

        $response->assertStatus(200)
            ->assertJson(['message' => 'All device tokens revoked successfully']);

        $this->assertEquals(0, $this->owner->tokens()->count());
    }

    public function test_login_rate_limiting_throttles_excessive_attempts(): void
    {
        RateLimiter::clear('owner_attack@seafood.com');

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/login', [
                'email' => 'owner_attack@seafood.com',
                'password' => 'wrong-pass',
            ]);
        }

        $response = $this->postJson('/api/v1/login', [
            'email' => 'owner_attack@seafood.com',
            'password' => 'wrong-pass',
        ]);

        $response->assertStatus(429);
    }

    public function test_kitchen_role_cannot_access_orders_or_reports(): void
    {
        $orderRes = $this->actingAs($this->kitchen)
            ->postJson('/api/v1/orders', [
                'order_table_id' => 1,
                'customer_name' => 'Forbidden Customer',
                'guest_count' => 2,
            ]);

        $orderRes->assertStatus(403);

        $reportRes = $this->actingAs($this->kitchen)
            ->getJson('/api/v1/reports/sales');

        $reportRes->assertStatus(403);
    }
}
