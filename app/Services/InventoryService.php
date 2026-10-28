<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\InventoryBatch;
use App\Models\StockMovement;
use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventoryService
{

    public function addStock(array $data): InventoryBatch
    {
        return DB::transaction(function () use ($data) {

            $batch = InventoryBatch::create([

                'outlet_id' => $data['outlet_id'],

                'ingredient_id' => $data['ingredient_id'],

                'quantity' => $data['quantity'],

                'remaining_quantity' => $data['quantity'],

                'cost_per_unit' => $data['cost_per_unit'],

                'source' => $data['source'] ?? null,

                'received_at' => now(),

            ]);



            StockMovement::create([

                'outlet_id' => $data['outlet_id'],

                'ingredient_id' => $data['ingredient_id'],

                'inventory_batch_id' => $batch->id,

                'type' => StockMovementType::In,

                'quantity' => $data['quantity'],

                'created_by' => auth()->id(),

                'note' => $data['note'] ?? null,

            ]);


            return $batch;
        });
    }

    public function getAvailableStock(
        int $ingredientId
    ): float {
        return InventoryBatch::where('ingredient_id', $ingredientId)
            ->sum('remaining_quantity');
    }

    public function deductStock(
        Ingredient $ingredient,
        float $quantity,
        ?Model $reference = null
    ): array {

        return DB::transaction(function () use (
            $ingredient,
            $quantity,
            $reference
        ) {

            $available = $this->getAvailableStock($ingredient->id);

            if ($available < $quantity) {
                throw new \DomainException(
                    "Stok {$ingredient->name} tidak mencukupi. Dibutuhkan {$quantity} {$ingredient->unit}, tersedia {$available} {$ingredient->unit}."
                );
            }

            $batches = InventoryBatch::query()
                ->where('ingredient_id', $ingredient->id)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('received_at')
                ->lockForUpdate()
                ->get();

            $remaining = $quantity;

            $totalCost = 0;

            $movements = [];

            foreach ($batches as $batch) {

                if ($remaining <= 0) {
                    break;
                }

                $deduct = min(
                    $batch->remaining_quantity,
                    $remaining
                );

                $batch->decrement(
                    'remaining_quantity',
                    $deduct
                );

                $cost = $deduct * $batch->cost_per_unit;

                $totalCost += $cost;

                StockMovement::create([

                    'outlet_id' => $batch->outlet_id,

                    'ingredient_id' => $ingredient->id,

                    'inventory_batch_id' => $batch->id,

                    'type' => StockMovementType::Sale,

                    'quantity' => $deduct,

                    'reference_type' => $reference
                        ? get_class($reference)
                        : null,

                    'reference_id' => $reference?->id,

                    'created_by' => auth()->id(),

                ]);

                $movements[] = [

                    'batch_id' => $batch->id,

                    'quantity' => $deduct,

                    'cost_per_unit' => $batch->cost_per_unit,

                    'cost' => $cost,

                ];

                $remaining -= $deduct;
            }

            return [

                'quantity' => $quantity,

                'cost' => $totalCost,

                'movements' => $movements,

            ];
        });
    }
}
