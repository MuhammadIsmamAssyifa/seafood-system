<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderTable;
use App\Models\Outlet;

class OrderTableSeeder extends Seeder
{
    public function run(): void
    {
        $outlet = Outlet::first();

        if (!$outlet) {
            return;
        }


        foreach (range(1, 10) as $number) {

            OrderTable::create([
                'outlet_id' => $outlet->id,
                'number' => $number,
                'status' => 'available',
            ]);
        }
    }
}
