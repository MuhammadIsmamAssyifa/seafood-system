<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\Outlet;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Seafood Bahari',
            'code' => 'SBH'
        ]);

        Outlet::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Outlet Utama',
            'code' => 'MAIN'
        ]);
    }
}
