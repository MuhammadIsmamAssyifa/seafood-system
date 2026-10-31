<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Collection;

class GlobalSearchService
{
    /**
     * Perform global search across primary domain models.
     *
     * @param string $query
     * @param int|null $outletId
     * @param int $limitPerCategory
     * @return array<string, mixed>
     */
    public function search(string $query, ?int $outletId = null, int $limitPerCategory = 5): array
    {
        $term = trim($query);

        if (mb_strlen($term) < 2) {
            return [
                'query' => $term,
                'total_results' => 0,
                'results' => [
                    'orders' => [],
                    'menus' => [],
                    'inventory' => [],
                    'employees' => [],
                    'recipes' => [],
                    'outlets' => [],
                ],
            ];
        }

        $likeTerm = "%{$term}%";

        // 1. Orders
        $ordersQuery = Order::query()
            ->with('orderTable')
            ->where(function ($q) use ($likeTerm) {
                $q->where('order_number', 'ILIKE', $likeTerm)
                  ->orWhere('customer_name', 'ILIKE', $likeTerm)
                  ->orWhereHas('orderTable', function ($t) use ($likeTerm) {
                      $t->where('number', 'ILIKE', $likeTerm);
                  });
            });

        if ($outletId) {
            $ordersQuery->where('outlet_id', $outletId);
        }

        $orders = $ordersQuery->latest()->limit($limitPerCategory)->get()->map(function ($order) {
            $statusStr = is_object($order->status) ? ($order->status->value ?? 'submitted') : (string) $order->status;
            $tableNum = $order->orderTable?->number ?? '-';
            return [
                'id' => $order->id,
                'type' => 'order',
                'title' => "Order #{$order->order_number}",
                'subtitle' => 'Pelanggan: ' . ($order->customer_name ?: 'Walk-in') . " • Meja {$tableNum}",
                'meta' => 'Rp ' . number_format((float) $order->grand_total, 0, ',', '.') . ' • ' . ucfirst($statusStr),
                'url' => "/orders/{$order->id}",
            ];
        });

        // 2. Menus
        $menusQuery = Menu::query()
            ->with('variants')
            ->where('name', 'ILIKE', $likeTerm);

        if ($outletId) {
            $menusQuery->where('outlet_id', $outletId);
        }

        $menus = $menusQuery->limit($limitPerCategory)->get()->map(function ($menu) {
            $minPrice = $menu->variants->min('price');
            $priceStr = $minPrice !== null ? 'Rp ' . number_format((float) $minPrice, 0, ',', '.') : 'Rp 0';
            $typeStr = is_object($menu->type) ? ($menu->type->value ?? 'menu') : (string) ($menu->type ?? 'menu');
            return [
                'id' => $menu->id,
                'type' => 'menu',
                'title' => $menu->name,
                'subtitle' => ucfirst($typeStr) . ' • ' . $menu->variants->count() . ' varian',
                'meta' => $priceStr,
                'url' => '/recipes',
            ];
        });

        // 3. Inventory / Ingredients
        $ingredientQuery = Ingredient::query()
            ->withSum('inventoryBatches as current_stock', 'remaining_quantity')
            ->where('name', 'ILIKE', $likeTerm);

        if ($outletId) {
            $ingredientQuery->where('outlet_id', $outletId);
        }

        $inventory = $ingredientQuery->limit($limitPerCategory)->get()->map(function ($item) {
            $stock = round((float) ($item->current_stock ?? 0), 2);
            $unit = is_object($item->unit) ? ($item->unit->value ?? 'unit') : (string) $item->unit;
            $typeStr = is_object($item->type) ? ($item->type->value ?? 'raw') : (string) ($item->type ?? 'raw');
            return [
                'id' => $item->id,
                'type' => 'inventory',
                'title' => $item->name,
                'subtitle' => "Stok tersisa: {$stock} {$unit}",
                'meta' => ucfirst($typeStr),
                'url' => "/inventory/{$item->id}",
            ];
        });

        // 4. Employees / Users
        $employees = User::query()
            ->with('roles')
            ->where(function ($q) use ($likeTerm) {
                $q->where('name', 'ILIKE', $likeTerm)
                  ->orWhere('email', 'ILIKE', $likeTerm)
                  ->orWhere('username', 'ILIKE', $likeTerm);
            })
            ->limit($limitPerCategory)
            ->get()
            ->map(function ($user) {
                $roleName = $user->roles->first()?->name ?? 'Staff';
                return [
                    'id' => $user->id,
                    'type' => 'employee',
                    'title' => $user->name,
                    'subtitle' => ($user->email ?: $user->username) . " • Role: {$roleName}",
                    'meta' => ucfirst($user->status ?? 'active'),
                    'url' => "/employees/{$user->id}",
                ];
            });

        // 5. Recipes
        $recipes = Recipe::query()
            ->with('menuVariant.menu')
            ->where('name', 'ILIKE', $likeTerm)
            ->limit($limitPerCategory)
            ->get()
            ->map(function ($recipe) {
                $menuName = $recipe->menuVariant?->menu?->name ?? 'Menu';
                return [
                    'id' => $recipe->id,
                    'type' => 'recipe',
                    'title' => $recipe->name,
                    'subtitle' => "Menu: {$menuName}",
                    'meta' => 'Resep Produksi',
                    'url' => "/recipes/{$recipe->id}",
                ];
            });

        // 6. Outlets
        $outlets = Outlet::query()
            ->where(function ($q) use ($likeTerm) {
                $q->where('name', 'ILIKE', $likeTerm)
                  ->orWhere('code', 'ILIKE', $likeTerm);
            })
            ->limit($limitPerCategory)
            ->get()
            ->map(function ($outlet) {
                return [
                    'id' => $outlet->id,
                    'type' => 'outlet',
                    'title' => $outlet->name,
                    'subtitle' => "Kode: {$outlet->code} • " . ($outlet->address ?: 'Cabang Restoran'),
                    'meta' => $outlet->status === 'active' ? 'Aktif' : 'Nonaktif',
                    'url' => "/outlets/{$outlet->id}",
                ];
            });

        $totalResults = $orders->count() + $menus->count() + $inventory->count() +
                        $employees->count() + $recipes->count() + $outlets->count();

        return [
            'query' => $term,
            'total_results' => $totalResults,
            'results' => [
                'orders' => $orders,
                'menus' => $menus,
                'inventory' => $inventory,
                'employees' => $employees,
                'recipes' => $recipes,
                'outlets' => $outlets,
            ],
        ];
    }
}
