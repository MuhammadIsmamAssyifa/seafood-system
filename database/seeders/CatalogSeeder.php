<?php

namespace Database\Seeders;

use App\Models\CookingMethod;
use App\Models\Ingredient;
use App\Models\KitchenStation;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Outlet;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run()
    {
        $outlet = Outlet::first();

        /*
        |--------------------------------------------------------------------------
        | Kitchen Station
        |--------------------------------------------------------------------------
        */

        $bakar = KitchenStation::create([
            'outlet_id' => $outlet->id,
            'name' => 'Bakar',
            'code' => 'BAKAR',
        ]);

        $masak = KitchenStation::create([
            'outlet_id' => $outlet->id,
            'name' => 'Masak',
            'code' => 'MASAK',
        ]);

        $minuman = KitchenStation::create([
            'outlet_id' => $outlet->id,
            'name' => 'Minuman',
            'code' => 'MINUM',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Ingredient
        |--------------------------------------------------------------------------
        */

        $cumi = Ingredient::create([
            'outlet_id' => $outlet->id,
            'name' => 'Cumi',
            'unit' => 'kg',
        ]);

        $udang = Ingredient::create([
            'outlet_id' => $outlet->id,
            'name' => 'Udang',
            'unit' => 'kg',
        ]);

        $ikan = Ingredient::create([
            'outlet_id' => $outlet->id,
            'name' => 'Ikan Etong',
            'unit' => 'kg',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Cooking Method
        |--------------------------------------------------------------------------
        */

        $crispy = CookingMethod::create([
            'outlet_id' => $outlet->id,
            'name' => 'Crispy',
        ]);

        $asamManis = CookingMethod::create([
            'outlet_id' => $outlet->id,
            'name' => 'Asam Manis',
        ]);

        $bakarMethod = CookingMethod::create([
            'outlet_id' => $outlet->id,
            'name' => 'Bakar',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Menu
        |--------------------------------------------------------------------------
        */

        $menuCumi = Menu::create([
            'outlet_id' => $outlet->id,
            'name' => 'Cumi',
            'type' => 'seafood',
        ]);

        $menuIkan = Menu::create([
            'outlet_id' => $outlet->id,
            'name' => 'Ikan Etong',
            'type' => 'seafood',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Menu Variant
        |--------------------------------------------------------------------------
        */

        MenuVariant::create([
            'outlet_id' => $outlet->id,
            'menu_id' => $menuCumi->id,
            'cooking_method_id' => $crispy->id,
            'main_ingredient_id' => $cumi->id,
            'kitchen_station_id' => $masak->id,
            'name' => 'Cumi Crispy',
            'price' => 35000,
            'sales_unit' => 'portion',
            'inventory_unit' => 'kg',
            'conversion_rate' => 0.5,
        ]);

        MenuVariant::create([
            'outlet_id' => $outlet->id,
            'menu_id' => $menuCumi->id,
            'cooking_method_id' => $asamManis->id,
            'main_ingredient_id' => $cumi->id,
            'kitchen_station_id' => $masak->id,
            'name' => 'Cumi Asam Manis',
            'price' => 35000,
            'sales_unit' => 'portion',
            'inventory_unit' => 'kg',
            'conversion_rate' => 0.5,
        ]);

        MenuVariant::create([
            'outlet_id' => $outlet->id,
            'menu_id' => $menuIkan->id,
            'cooking_method_id' => $bakarMethod->id,
            'main_ingredient_id' => $ikan->id,
            'kitchen_station_id' => $bakar->id,
            'name' => 'Ikan Etong Bakar',
            'price' => 70000,
            'sales_unit' => 'kg',
            'inventory_unit' => 'kg',
            'conversion_rate' => 1,
        ]);
    }
}
