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
            // User & Employee Management
            'user.view',
            'user.manage',
            'employee.view',
            'employee.create',
            'employee.update',
            'employee.manage',

            // Role & Permission Management
            'role.view',
            'role.manage',

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

            // Inventory & Waste
            'inventory.view',
            'inventory.manage',
            'inventory.adjust',
            'waste.view',
            'waste.manage',

            // Dashboard & Reporting
            'dashboard.view',
            'report.view',

            // Audit & System Settings (Sprint 11 & 12)
            'audit.view',
            'settings.view',
            'settings.update',
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
            'employee.view',
            'employee.create',
            'employee.update',
            'employee.manage',
            'role.view',
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
            'waste.view',
            'waste.manage',
            'dashboard.view',
            'report.view',
            'audit.view',
            'settings.view',
            'settings.update',
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
