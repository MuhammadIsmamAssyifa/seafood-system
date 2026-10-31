<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $variant = $this->menuVariant;
        $menu = $variant?->menu;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'menu_variant' => $variant ? [
                'id' => $variant->id,
                'name' => $variant->name,
                'price' => (float) $variant->price,
                'menu_id' => $menu?->id,
                'menu_name' => $menu?->name,
                'outlet_id' => $variant->outlet_id,
                'outlet_name' => $variant->outlet?->name,
            ] : null,
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'ingredient_id' => $item->ingredient_id,
                    'ingredient_name' => $item->ingredient?->name,
                    'quantity' => (float) $item->quantity,
                    'unit' => $item->unit instanceof \BackedEnum ? $item->unit->value : $item->unit,
                ];
            }),
            'items_count' => $this->items->count(),
            'cost_calculation' => $this->cost_calculation ?? null,
            'creator' => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ] : null,
            'created_at' => $this->created_at?->toISOString() ?? (string) $this->created_at,
            'updated_at' => $this->updated_at?->toISOString() ?? (string) $this->updated_at,
        ];
    }
}

