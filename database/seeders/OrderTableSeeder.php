<?php

namespace Database\Seeders;

use App\Models\OrderTable;
use App\Models\Outlet;
use Illuminate\Database\Seeder;

class OrderTableSeeder extends Seeder
{
    public function run(): void
    {
        $outlet = Outlet::first();

        if (! $outlet) {
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
