<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management
            'user.view',
            'user.manage',

            // Menu & Catalog
            'menu.view',
            'menu.manage',

            // Order
            'order.view',
            'order.create',
            'order.update',
            'order.cancel',
            'order.change',

            // Kitchen
            'kitchen.view',
            'kitchen.update',

            // Payment
            'payment.view',
            'payment.create',

            // Inventory
            'inventory.view',
            'inventory.manage',
            'inventory.adjust',

            // Dashboard & Reporting
            'dashboard.view',
            'report.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Roles
        $owner = Role::where('name', 'owner')->first();
        $admin = Role::where('name', 'admin')->first();
        $cashier = Role::where('name', 'cashier')->first();
        $kasir = Role::where('name', 'kasir')->first();
        $kitchen = Role::where('name', 'kitchen')->first();
        $kitchenHead = Role::where('name', 'kitchen_head')->first();
        $kitchenStaff = Role::where('name', 'kitchen_staff')->first();

        // 1. Owner: All permissions
        $owner?->syncPermissions(Permission::all());

        // 2. Admin: Full operational permissions
        $adminPermissions = [
            'user.view',
            'user.manage',
            'menu.view',
            'menu.manage',
            'order.view',
            'order.create',
            'order.update',
            'order.cancel',
            'order.change',
            'kitchen.view',
            'kitchen.update',
            'payment.view',
            'payment.create',
            'inventory.view',
            'inventory.manage',
            'inventory.adjust',
            'dashboard.view',
            'report.view',
        ];
        $admin?->syncPermissions($adminPermissions);

        // 3. Cashier / Kasir
        $cashierPermissions = [
            'menu.view',
            'order.view',
            'order.create',
            'order.update',
            'order.cancel',
            'order.change',
            'payment.view',
            'payment.create',
            'dashboard.view',
        ];
        $cashier?->syncPermissions($cashierPermissions);
        $kasir?->syncPermissions($cashierPermissions);

        // 4. Kitchen / Kitchen Staff / Head
        $kitchenPermissions = [
            'menu.view',
            'kitchen.view',
            'kitchen.update',
            'inventory.view',
        ];
        $kitchen?->syncPermissions($kitchenPermissions);
        $kitchenHead?->syncPermissions($kitchenPermissions);
        $kitchenStaff?->syncPermissions($kitchenPermissions);
    }
}
