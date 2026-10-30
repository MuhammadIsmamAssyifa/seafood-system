<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KitchenAuthTest extends TestCase
{
    protected Restaurant $restaurant;
    protected Outlet $outlet;
    protected User $kitchenUser;
    protected User $cashierUser;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
        (new PermissionSeeder)->run();

        $this->restaurant = Restaurant::firstOrCreate(
            ['code' => 'SEAFOOD-RESTO-TEST'],
            ['name' => 'Seafood Resto Test', 'status' => 'active']
        );

        $this->outlet = Outlet::firstOrCreate(
            ['code' => 'OUTLET-KDS-TEST'],
            ['restaurant_id' => $this->restaurant->id, 'name' => 'KDS Outlet Test', 'status' => 'active']
        );

        $this->kitchenUser = User::firstOrCreate(
            ['username' => 'chef_andi'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Chef Andi',
                'email' => 'chef.andi@example.com',
                'password' => Hash::make('password123'),
                'pin_hash' => Hash::make('654321'),
                'status' => 'active',
            ]
        );
        $this->kitchenUser->syncRoles(['kitchen']);
        $this->kitchenUser->outlets()->sync([$this->outlet->id]);

        $this->cashierUser = User::firstOrCreate(
            ['username' => 'kasir_budi'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Kasir Budi',
                'email' => 'kasir.budi@example.com',
                'password' => Hash::make('password123'),
                'pin_hash' => Hash::make('1234'),
                'status' => 'active',
            ]
        );
        $this->cashierUser->syncRoles(['cashier']);
        $this->cashierUser->outlets()->sync([$this->outlet->id]);
    }

    public function test_kitchen_staff_can_login_with_pin(): void
    {
        $response = $this->postJson('/api/v1/staff-login', [
            'username' => 'chef_andi',
            'pin' => '654321',
            'device_name' => 'kds-screen',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'username',
                    'roles',
                    'permissions',
                ],
            ]);

        $roles = $response->json('user.roles');
        $this->assertContains('kitchen', $roles);
    }

    public function test_kitchen_user_can_login_with_password(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'chef.andi@example.com',
            'password' => 'password123',
            'device_name' => 'kds-screen',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'roles',
                    'permissions',
                ],
            ]);
    }

    public function test_authenticated_user_can_fetch_profile_via_me_endpoint(): void
    {
        $token = $this->kitchenUser->createToken('kds-test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.username', 'chef_andi')
            ->assertJsonPath('data.role', 'kitchen');

        $permissions = $response->json('data.permissions');
        $this->assertContains('kitchen.view', $permissions);
    }

    public function test_non_kitchen_user_lacks_kitchen_view_permission(): void
    {
        $token = $this->cashierUser->createToken('cashier-test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me');

        $response->assertStatus(200);
        $permissions = $response->json('data.permissions') ?? [];
        $this->assertNotContains('kitchen.view', $permissions);
    }

    public function test_kitchen_user_can_logout_and_revoke_token(): void
    {
        $token = $this->kitchenUser->createToken('kds-test-logout')->plainTextToken;

        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);

        $this->assertCount(0, $this->kitchenUser->tokens()->where('name', 'kds-test-logout')->get());
    }

    public function test_login_with_invalid_credentials_returns_401(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'chef.andi@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }
}
