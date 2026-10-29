<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'name' => $this->name,

            'type' => $this->type,

            'variants' => $this->variants
                ->map(function ($variant) {

                    return [

                        'id' => $variant->id,

                        'name' => $variant->name,

                        'price' => (float) $variant->price,

                        'sales_unit' => $variant->sales_unit,

                        'inventory_unit' => $variant->inventory_unit,

                        'conversion_rate' => (float) $variant->conversion_rate,

                        'ingredient' => $variant
                            ->mainIngredient?->name,

                        'station' => $variant
                            ->kitchenStation?->name,

                        'cooking_method' => $variant
                            ->cookingMethod?->name,

                    ];
                }),

        ];
    }
}
