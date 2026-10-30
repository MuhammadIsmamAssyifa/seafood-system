<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $restaurant = Restaurant::first();

        $outlet = Outlet::first();

        /*
        |--------------------------------------------------------------------------
        | Owner
        |--------------------------------------------------------------------------
        */

        $owner = User::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Owner Seafood',
            'email' => 'owner@seafood.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $owner->assignRole('owner');

        /*
        |--------------------------------------------------------------------------
        | Cashier
        |--------------------------------------------------------------------------
        */

        $cashier = User::create([

            'restaurant_id' => $restaurant->id,
            'name' => 'Kasir 01',
            'username' => 'kasir01',
            'pin_hash' => Hash::make('123456'),
            'status' => 'active',
        ]);

        $cashier->assignRole('cashier');

        /*
        |--------------------------------------------------------------------------
        | Kitchen Head
        |--------------------------------------------------------------------------
        */

        $kitchenHead = User::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Kepala Kitchen',
            'username' => 'kitchen01',
            'pin_hash' => Hash::make('123456'),
            'status' => 'active',

        ]);

        $kitchenHead->assignRole('kitchen_head');

        /*
        |--------------------------------------------------------------------------
        | Kitchen Staff
        |--------------------------------------------------------------------------
        */

        $kitchenStaff = User::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Kitchen Staff',
            'username' => 'kitchen02',
            'pin_hash' => Hash::make('123456'),
            'status' => 'active',
        ]);

        $kitchenStaff->assignRole('kitchen_staff');

        /*
        |--------------------------------------------------------------------------
        | Outlet Access
        |--------------------------------------------------------------------------
        */

        $owner->outlets()->attach($outlet->id);
        $cashier->outlets()->attach($outlet->id);
        $kitchenHead->outlets()->attach($outlet->id);
        $kitchenStaff->outlets()->attach($outlet->id);
    }
}
