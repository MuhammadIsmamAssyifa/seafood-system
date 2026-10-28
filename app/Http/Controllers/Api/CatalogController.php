<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KitchenStationResource;
use App\Http\Resources\MenuResource;
use App\Models\KitchenStation;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogController extends Controller
{
    public function menus(Request $request): AnonymousResourceCollection
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id;

        $menus = Menu::with([
            'variants.cookingMethod',
            'variants.mainIngredient',
            'variants.kitchenStation',
        ])
            ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->where('status', 'active')
            ->get();

        return MenuResource::collection($menus);
    }

    public function stations(Request $request): AnonymousResourceCollection
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id;

        $stations = KitchenStation::when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->where('status', 'active')
            ->get();

        return KitchenStationResource::collection($stations);
    }
}
