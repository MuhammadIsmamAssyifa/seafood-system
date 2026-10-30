<?php

namespace App\Http\Resources;

use App\Models\InventoryBatch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    protected static array $stockCache = [];

    public static function clearStockCache(): void
    {
        static::$stockCache = [];
    }

    public function toArray(Request $request): array
    {
        $outletId = $this->outlet_id ?? $request->user()?->outlets()?->first()?->id ?? 1;

        if (! isset(static::$stockCache[$outletId])) {
            static::$stockCache[$outletId] = InventoryBatch::where('outlet_id', $outletId)
                ->where('remaining_quantity', '>', 0)
                ->groupBy('ingredient_id')
                ->selectRaw('ingredient_id, SUM(remaining_quantity) as total_stock')
                ->pluck('total_stock', 'ingredient_id')
                ->all();
        }

        $stocks = static::$stockCache[$outletId];
        $menuType = $this->type instanceof \BackedEnum ? $this->type->value : (string) $this->type;
        $isSeafood = ($menuType === 'seafood');

        $variantsData = $this->variants->map(function ($variant) use ($stocks, $isSeafood) {
            $ingredientId = $variant->main_ingredient_id;

            if (! $ingredientId) {
                $stockQuantity = null;
                $stockStatus = 'available';
                $needMarketCheck = false;
            } else {
                $rawRemaining = (float) ($stocks[$ingredientId] ?? 0);
                $conv = (float) ($variant->conversion_rate > 0 ? $variant->conversion_rate : 1);
                $stockQuantity = round($rawRemaining / $conv, 2);

                if ($stockQuantity > 5) {
                    $stockStatus = 'available';
                    $needMarketCheck = false;
                } elseif ($stockQuantity > 0) {
                    $stockStatus = 'low_stock';
                    $needMarketCheck = false;
                } else {
                    if ($isSeafood) {
                        $stockStatus = 'need_market_check';
                        $needMarketCheck = true;
                    } else {
                        $stockStatus = 'out_of_stock';
                        $needMarketCheck = false;
                    }
                }
            }

            return [
                'id' => $variant->id,
                'name' => $variant->name,
                'price' => (float) $variant->price,
                'sales_unit' => $variant->sales_unit,
                'inventory_unit' => $variant->inventory_unit,
                'conversion_rate' => (float) $variant->conversion_rate,
                'ingredient' => $variant->mainIngredient?->name,
                'station' => $variant->kitchenStation?->name,
                'cooking_method' => $variant->cookingMethod?->name,
                'stock_status' => $stockStatus,
                'stock_quantity' => $stockQuantity,
                'need_market_check' => $needMarketCheck,
            ];
        });

        // Determine parent menu stock summary
        $parentStatus = 'available';
        $parentNeedMarketCheck = false;
        $parentQuantity = null;

        if ($variantsData->isNotEmpty()) {
            $hasAvailable = $variantsData->contains(fn ($v) => $v['stock_status'] === 'available');
            $hasLowStock = $variantsData->contains(fn ($v) => $v['stock_status'] === 'low_stock');
            $hasMarketCheck = $variantsData->contains(fn ($v) => $v['stock_status'] === 'need_market_check');
            $allOutOfStock = $variantsData->every(fn ($v) => $v['stock_status'] === 'out_of_stock');

            if ($allOutOfStock) {
                $parentStatus = 'out_of_stock';
            } elseif ($hasAvailable) {
                $parentStatus = 'available';
            } elseif ($hasLowStock) {
                $parentStatus = 'low_stock';
            } elseif ($hasMarketCheck) {
                $parentStatus = 'need_market_check';
                $parentNeedMarketCheck = true;
            }

            $quantities = $variantsData->pluck('stock_quantity')->filter(fn ($q) => ! is_null($q));
            if ($quantities->isNotEmpty()) {
                $parentQuantity = (float) $quantities->max();
            }
            if ($variantsData->every(fn ($v) => $v['need_market_check'] === true)) {
                $parentNeedMarketCheck = true;
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'stock_status' => $parentStatus,
            'stock_quantity' => $parentQuantity,
            'need_market_check' => $parentNeedMarketCheck,
            'variants' => $variantsData,
        ];
    }
}
