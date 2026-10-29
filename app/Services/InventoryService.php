<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\OrderItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function addStock(array $data): InventoryBatch
    {
        return DB::transaction(function () use ($data) {
            $totalCost = round($data['quantity'] * $data['cost_per_unit'], 2);

            $batch = InventoryBatch::create([
                'outlet_id' => $data['outlet_id'],
                'ingredient_id' => $data['ingredient_id'],
                'quantity' => $data['quantity'],
                'remaining_quantity' => $data['quantity'],
                'cost_per_unit' => $data['cost_per_unit'],
                'source' => $data['source'] ?? null,
                'received_at' => $data['received_at'] ?? now(),
            ]);

            $movement = StockMovement::create([
                'outlet_id' => $data['outlet_id'],
                'ingredient_id' => $data['ingredient_id'],
                'inventory_batch_id' => $batch->id,
                'type' => StockMovementType::In,
                'quantity' => $data['quantity'],
                'cost_per_unit' => $data['cost_per_unit'],
                'total_cost' => $totalCost,
                'created_by' => auth()->id() ?? $data['created_by'] ?? User::first()?->id,
                'note' => $data['note'] ?? null,
            ]);

            $this->auditLogService->logInventoryAdjustment($movement, $data['note'] ?? 'Penambahan stok');

            return $batch;
        });
    }

    public function getAvailableStock(int $ingredientId): float
    {
        return (float) InventoryBatch::where('ingredient_id', $ingredientId)
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
                $unit = $ingredient->unit instanceof \BackedEnum ? $ingredient->unit->value : $ingredient->unit;
                throw new \DomainException(
                    "Stok {$ingredient->name} tidak mencukupi. Dibutuhkan {$quantity} {$unit}, tersedia {$available} {$unit}."
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
                    (float) $batch->remaining_quantity,
                    $remaining
                );

                $batch->decrement('remaining_quantity', $deduct);

                $cost = round($deduct * (float) $batch->cost_per_unit, 2);
                $totalCost += $cost;

                StockMovement::create([
                    'outlet_id' => $batch->outlet_id,
                    'ingredient_id' => $ingredient->id,
                    'inventory_batch_id' => $batch->id,
                    'type' => StockMovementType::Sale,
                    'quantity' => $deduct,
                    'cost_per_unit' => $batch->cost_per_unit,
                    'total_cost' => $cost,
                    'reference_type' => $reference ? get_class($reference) : null,
                    'reference_id' => $reference?->id,
                    'created_by' => auth()->id() ?? ($reference instanceof OrderItem ? $reference->orderBatch?->order?->created_by : null) ?? User::first()?->id,
                ]);

                $movements[] = [
                    'batch_id' => $batch->id,
                    'quantity' => $deduct,
                    'cost_per_unit' => (float) $batch->cost_per_unit,
                    'cost' => $cost,
                ];

                $remaining -= $deduct;
            }

            return [
                'quantity' => $quantity,
                'cost' => round($totalCost, 2),
                'movements' => $movements,
            ];
        });
    }
}
