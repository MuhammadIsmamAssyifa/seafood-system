<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeAndPermissionTest extends TestCase
{
    protected Restaurant $restaurant;
    protected Outlet $outlet;
    protected User $owner;
    protected User $admin;
    protected User $cashier;
    protected User $kitchen;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
        (new PermissionSeeder)->run();

        $this->restaurant = Restaurant::firstOrCreate(
            ['code' => 'RESTO-EMP-TEST'],
            ['name' => 'Resto Employee Test', 'status' => 'active']
        );

        $this->outlet = Outlet::firstOrCreate(
            ['code' => 'OUTLET-EMP-1'],
            ['restaurant_id' => $this->restaurant->id, 'name' => 'Outlet Emp 1', 'status' => 'active']
        );

        $this->owner = User::firstOrCreate(
            ['email' => 'owner_emp@test.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Owner Emp',
                'username' => 'owner_emp',
                'password' => bcrypt('password123'),
                'status' => 'active',
            ]
        );
        $this->owner->syncRoles(['owner']);
        $this->owner->outlets()->sync([$this->outlet->id]);

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_emp@test.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Admin Emp',
                'username' => 'admin_emp',
                'password' => bcrypt('password123'),
                'status' => 'active',
            ]
        );
        $this->admin->syncRoles(['admin']);
        $this->admin->outlets()->sync([$this->outlet->id]);

        $this->cashier = User::firstOrCreate(
            ['email' => 'cashier_emp@test.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Cashier Emp',
                'username' => 'cashier_emp',
                'password' => bcrypt('password123'),
                'status' => 'active',
            ]
        );
        $this->cashier->syncRoles(['cashier']);

        $this->kitchen = User::firstOrCreate(
            ['email' => 'kitchen_emp@test.com'],
            [
                'restaurant_id' => $this->restaurant->id,
                'name' => 'Kitchen Emp',
                'username' => 'kitchen_emp',
                'password' => bcrypt('password123'),
                'status' => 'active',
            ]
        );
        $this->kitchen->syncRoles(['kitchen']);
    }

    public function test_owner_can_list_employees_with_summary(): void
    {
        $token = $this->owner->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/employees');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'username', 'email', 'status', 'role', 'roles', 'outlets'],
                ],
                'meta' => ['current_page', 'last_page', 'total'],
                'summary' => ['total', 'active', 'inactive', 'role_counts'],
            ]);
    }

    public function test_owner_can_create_employee_with_role_and_outlet(): void
    {
        $token = $this->owner->createToken('test')->plainTextToken;
        $unique = uniqid();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/employees', [
                'name' => 'Budi Santoso',
                'username' => 'budi_' . $unique,
                'email' => "budi_{$unique}@example.com",
                'password' => 'secret123',
                'pin' => '1234',
                'role' => 'cashier',
                'outlet_ids' => [$this->outlet->id],
                'status' => 'active',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Budi Santoso')
            ->assertJsonPath('data.role', 'cashier')
            ->assertJsonPath('data.has_pin', true);

        $newUserId = $response->json('data.id');

        $this->assertDatabaseHas('users', [
            'id' => $newUserId,
            'email' => "budi_{$unique}@example.com",
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('user_outlets', [
            'user_id' => $newUserId,
            'outlet_id' => $this->outlet->id,
        ]);
    }

    public function test_employee_creation_fails_on_duplicate_username_or_email(): void
    {
        $token = $this->owner->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/employees', [
                'name' => 'Duplicate Test',
                'username' => $this->owner->username,
                'email' => $this->owner->email,
                'password' => 'secret123',
                'role' => 'cashier',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'email']);
    }

    public function test_can_update_employee_details_and_roles(): void
    {
        $token = $this->owner->createToken('test')->plainTextToken;

        $targetUser = User::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Original Name',
            'username' => 'target_' . uniqid(),
            'email' => 'target_' . uniqid() . '@test.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $targetUser->syncRoles(['cashier']);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/employees/{$targetUser->id}", [
                'name' => 'Updated Name',
                'username' => $targetUser->username,
                'email' => $targetUser->email,
                'role' => 'kitchen',
                'outlet_ids' => [$this->outlet->id],
                'status' => 'active',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.role', 'kitchen');

        $this->assertTrue($targetUser->fresh()->hasRole('kitchen'));
    }

    public function test_can_activate_and_deactivate_employee_status(): void
    {
        $token = $this->owner->createToken('test')->plainTextToken;

        $targetUser = User::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Staff Inactive Test',
            'username' => 'deact_' . uniqid(),
            'email' => 'deact_' . uniqid() . '@test.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $resDeact = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/employees/{$targetUser->id}/status", [
                'status' => 'inactive',
            ]);

        $resDeact->assertStatus(200)
            ->assertJsonPath('data.status', 'inactive');

        $this->assertEquals('inactive', $targetUser->fresh()->status);
    }

    public function test_user_cannot_deactivate_themselves(): void
    {
        $token = $this->owner->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/employees/{$this->owner->id}/status", [
                'status' => 'inactive',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Anda tidak dapat mengubah status akun Anda sendiri.');
    }

    public function test_can_assign_outlets_to_employee(): void
    {
        $token = $this->owner->createToken('test')->plainTextToken;

        $targetUser = User::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Outlet Assignee',
            'username' => 'assignee_' . uniqid(),
            'email' => 'assignee_' . uniqid() . '@test.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/employees/{$targetUser->id}/outlets", [
                'outlet_ids' => [$this->outlet->id],
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.outlets.0.id', $this->outlet->id);

        $this->assertDatabaseHas('user_outlets', [
            'user_id' => $targetUser->id,
            'outlet_id' => $this->outlet->id,
        ]);
    }

    public function test_can_fetch_roles_and_permissions_matrix(): void
    {
        $token = $this->owner->createToken('test')->plainTextToken;

        // Roles list
        $resRoles = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/roles');

        $resRoles->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'label', 'user_count', 'permission_count', 'permissions', 'is_system'],
                ],
            ]);

        // Permissions catalog
        $resPerms = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/roles/permissions');

        $resPerms->assertStatus(200)
            ->assertJsonStructure(['data', 'grouped']);

        // Permission Matrix
        $resMatrix = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/roles/matrix');

        $resMatrix->assertStatus(200)
            ->assertJsonStructure([
                'roles',
                'matrix' => [
                    '*' => ['permission', 'label', 'roles'],
                ],
            ]);
    }

    public function test_owner_can_create_role_and_update_permissions(): void
    {
        $token = $this->owner->createToken('test')->plainTextToken;
        $unique = uniqid();

        // Create Role
        $resCreate = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/roles', [
                'name' => 'Supervisor ' . $unique,
                'permissions' => ['order.view', 'kitchen.view'],
            ]);

        $resCreate->assertStatus(201);
        $roleId = $resCreate->json('data.id');

        // Update Role Permissions
        $resUpdate = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/roles/{$roleId}", [
                'permissions' => ['order.view', 'kitchen.view', 'inventory.view'],
            ]);

        $resUpdate->assertStatus(200)
            ->assertJsonPath('data.permissions', ['order.view', 'kitchen.view', 'inventory.view']);
    }

    public function test_cannot_modify_owner_role_permissions(): void
    {
        $token = $this->owner->createToken('test')->plainTextToken;
        $ownerRole = Role::findByName('owner');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/roles/{$ownerRole->id}", [
                'permissions' => ['order.view'],
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Hak akses Role Owner bersifat permanen dan tidak dapat diubah.');
    }

    public function test_kitchen_or_cashier_user_forbidden_from_employee_management(): void
    {
        $tokenCashier = $this->cashier->createToken('cashier')->plainTextToken;
        $resCashier = $this->withHeader('Authorization', "Bearer {$tokenCashier}")
            ->getJson('/api/v1/employees');
        $resCashier->assertStatus(403);

        $tokenKitchen = $this->kitchen->createToken('kitchen')->plainTextToken;
        $resKitchen = $this->withHeader('Authorization', "Bearer {$tokenKitchen}")
            ->getJson('/api/v1/employees');
        $resKitchen->assertStatus(403);
    }
}

