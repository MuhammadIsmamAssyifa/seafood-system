<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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

            // User
            'user.manage',

            // Menu
            'menu.view',
            'menu.manage',

            // Order
            'order.create',
            'order.view',
            'order.cancel',

            // Kitchen
            'kitchen.view',
            'kitchen.update',

            // Payment
            'payment.create',

            // Inventory
            'inventory.view',
            'inventory.adjust',

            // Report
            'report.view',

        ];

        foreach ($permissions as $permission) {

            Permission::create([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        $owner = Role::where('name', 'owner')->first();
        $admin = Role::where('name', 'admin')->first();
        $cashier = Role::where('name', 'cashier')->first();
        $kitchenHead = Role::where('name', 'kitchen_head')->first();
        $kitchenStaff = Role::where('name', 'kitchen_staff')->first();

        $owner->givePermissionTo(Permission::all());
        $admin->givePermissionTo([
            'user.manage',
            'menu.manage',
            'report.view'
        ]);
        $cashier->givePermissionTo([
            'order.create',
            'order.view',
            'payment.create'
        ]);
        $kitchenHead->givePermissionTo([
            'kitchen.view',
            'kitchen.update'
        ]);
        $kitchenStaff->givePermissionTo([
            'kitchen.view',
            'kitchen.update'
        ]);
    }
}
