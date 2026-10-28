<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\KitchenStation;
use App\Http\Resources\MenuResource;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function menus(Request $request)
    {

        $outletId = $request->user()
            ->outlets()
            ->first()
            ->id;


        $menus = Menu::with([
            'variants.cookingMethod',
            'variants.mainIngredient',
            'variants.kitchenStation'
        ])
            ->where('outlet_id', $outletId)
            ->where('status', 'active')
            ->get();



        return MenuResource::collection($menus);
    }



    public function stations(Request $request)
    {

        $outletId = $request->user()
            ->outlets()
            ->first()
            ->id;


        $stations = KitchenStation::where(
            'outlet_id',
            $outletId
        )
            ->where(
                'status',
                'active'
            )
            ->get();



        return response()->json([
            'data' => $stations
        ]);
    }
}
