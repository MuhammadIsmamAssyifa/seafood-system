<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Seafood Bahari',
            'code' => 'SBH',
        ]);

        Outlet::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Outlet Utama',
            'code' => 'MAIN',
        ]);
    }
}
