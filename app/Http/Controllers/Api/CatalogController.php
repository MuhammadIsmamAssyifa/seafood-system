<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KitchenStationResource;
use App\Http\Resources\MenuResource;
use App\Models\KitchenStation;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class CatalogController extends Controller
{
    public function menus(Request $request): AnonymousResourceCollection
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id ?? 1;

        if ($request->boolean('refresh') || $request->has('fresh') || $request->header('Cache-Control') === 'no-cache') {
            Cache::forget("catalog_menus_outlet_{$outletId}");
            MenuResource::clearStockCache();
        }

        $menus = Cache::remember("catalog_menus_outlet_{$outletId}", 300, function () use ($outletId) {
            return Menu::with([
                'variants.cookingMethod',
                'variants.mainIngredient',
                'variants.kitchenStation',
            ])
                ->where('outlet_id', $outletId)
                ->where('status', 'active')
                ->get();
        });

        return MenuResource::collection($menus);
    }

    public function stations(Request $request): AnonymousResourceCollection
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id ?? 1;

        $stations = Cache::remember("catalog_stations_outlet_{$outletId}", 300, function () use ($outletId) {
            return KitchenStation::where('outlet_id', $outletId)
                ->where('status', 'active')
                ->get();
        });

        return KitchenStationResource::collection($stations);
    }
}
