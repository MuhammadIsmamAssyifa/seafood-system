<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\MenuVariant;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecipeService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Get paginated recipes list with cost calculations.
     */
    public function getRecipes(
        ?int $outletId = null,
        ?string $search = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Recipe::query()
            ->with(['menuVariant.menu', 'menuVariant.outlet', 'items.ingredient', 'creator'])
            ->latest();

        if ($outletId) {
            $query->whereHas('menuVariant', function ($q) use ($outletId) {
                $q->where('outlet_id', $outletId);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhereHas('menuVariant', function ($mq) use ($search) {
                        $mq->where('name', 'ILIKE', "%{$search}%")
                            ->orWhereHas('menu', function ($mmq) use ($search) {
                                $mmq->where('name', 'ILIKE', "%{$search}%");
                            });
                    });
            });
        }

        $paginator = $query->paginate($perPage);

        // Attach dynamic cost calculations to each recipe
        $paginator->getCollection()->transform(function (Recipe $recipe) {
            $calc = $this->calculateCost($recipe);
            $recipe->cost_calculation = $calc;
            return $recipe;
        });

        return $paginator;
    }

    /**
     * Get single recipe detail with cost and margin breakdown.
     */
    public function getDetail(int $id): ?Recipe
    {
        $recipe = Recipe::with([
            'menuVariant.menu',
            'menuVariant.cookingMethod',
            'menuVariant.kitchenStation',
            'menuVariant.outlet',
            'items.ingredient.inventoryBatches' => fn ($q) => $q->where('remaining_quantity', '>', 0),
            'creator'
        ])->find($id);

        if (! $recipe) {
            return null;
        }

        $recipe->cost_calculation = $this->calculateCost($recipe);
        return $recipe;
    }

    /**
     * Calculate ingredient cost, total recipe cost, food cost % and gross margin.
     */
    public function calculateCost(Recipe $recipe): array
    {
        $recipe->loadMissing(['items.ingredient', 'menuVariant']);

        $totalCost = 0.0;
        $itemsBreakdown = [];

        foreach ($recipe->items as $item) {
            $ingredient = $item->ingredient;
            $qty = (float) $item->quantity;

            // Determine unit cost using active batches average, fallback to latest batch
            $batches = InventoryBatch::where('ingredient_id', $item->ingredient_id)
                ->where('remaining_quantity', '>', 0)
                ->get();

            $costPerUnit = 0.0;
            if ($batches->isNotEmpty()) {
                $totalRemQty = (float) $batches->sum('remaining_quantity');
                $totalVal = (float) $batches->sum(fn ($b) => (float) $b->remaining_quantity * (float) $b->cost_per_unit);
                $costPerUnit = $totalRemQty > 0 ? round($totalVal / $totalRemQty, 2) : 0.0;
            } else {
                $latest = InventoryBatch::where('ingredient_id', $item->ingredient_id)
                    ->latest('received_at')
                    ->first();
                $costPerUnit = $latest ? (float) $latest->cost_per_unit : 0.0;
            }

            // Normalise unit conversion if needed (gram to kg, ml to liter)
            $itemUnit = $item->unit instanceof \BackedEnum ? $item->unit->value : (string) $item->unit;
            $ingUnit = $ingredient?->unit instanceof \BackedEnum ? $ingredient->unit->value : (string) $ingredient?->unit;

            $normalizedFactor = 1.0;
            if ($itemUnit === 'gram' && $ingUnit === 'kg') {
                $normalizedFactor = 0.001;
            } elseif ($itemUnit === 'kg' && $ingUnit === 'gram') {
                $normalizedFactor = 1000.0;
            } elseif ($itemUnit === 'ml' && $ingUnit === 'liter') {
                $normalizedFactor = 0.001;
            } elseif ($itemUnit === 'liter' && $ingUnit === 'ml') {
                $normalizedFactor = 1000.0;
            }

            $effectiveCost = round($qty * $normalizedFactor * $costPerUnit, 2);
            $totalCost += $effectiveCost;

            $itemsBreakdown[] = [
                'id' => $item->id,
                'ingredient_id' => $item->ingredient_id,
                'ingredient_name' => $ingredient?->name ?? 'Bahan #' . $item->ingredient_id,
                'quantity' => $qty,
                'unit' => $itemUnit,
                'ingredient_unit' => $ingUnit,
                'unit_cost' => $costPerUnit,
                'subtotal_cost' => $effectiveCost,
            ];
        }

        $sellingPrice = (float) ($recipe->menuVariant?->price ?? 0);
        $totalCost = round($totalCost, 2);
        $grossMargin = round(max(0, $sellingPrice - $totalCost), 2);
        $foodCostPercent = $sellingPrice > 0 ? round(($totalCost / $sellingPrice) * 100, 1) : 0.0;
        $marginPercent = $sellingPrice > 0 ? round(($grossMargin / $sellingPrice) * 100, 1) : 0.0;

        return [
            'total_cost' => $totalCost,
            'selling_price' => $sellingPrice,
            'gross_margin' => $grossMargin,
            'food_cost_percentage' => $foodCostPercent,
            'margin_percentage' => $marginPercent,
            'items' => $itemsBreakdown,
        ];
    }

    /**
     * Create new recipe for a menu variant.
     */
    public function create(array $data, ?User $user = null): Recipe
    {
        $menuVariantId = $data['menu_variant_id'];

        // Enforce 1 recipe per variant
        if (Recipe::where('menu_variant_id', $menuVariantId)->exists()) {
            throw ValidationException::withMessages([
                'menu_variant_id' => ['Varian menu ini sudah memiliki resep. Gunakan fitur edit untuk memperbarui resep.'],
            ]);
        }

        $variant = MenuVariant::with('menu')->findOrFail($menuVariantId);

        return DB::transaction(function () use ($data, $variant, $user) {
            $recipe = Recipe::create([
                'menu_variant_id' => $variant->id,
                'name' => $data['name'] ?? ($variant->menu?->name . ' - ' . $variant->name),
                'description' => $data['description'] ?? null,
                'created_by' => $user?->id ?? auth()->id(),
            ]);

            // Prevent duplicate ingredients in same recipe
            $usedIngredients = [];
            foreach ($data['items'] as $itemData) {
                if (in_array($itemData['ingredient_id'], $usedIngredients)) {
                    throw ValidationException::withMessages([
                        'items' => ['Bahan baku yang sama tidak boleh dimasukkan lebih dari satu kali.'],
                    ]);
                }
                $usedIngredients[] = $itemData['ingredient_id'];

                $recipe->items()->create([
                    'ingredient_id' => $itemData['ingredient_id'],
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                ]);
            }

            // Log to Audit Log
            $calc = $this->calculateCost($recipe->load('items.ingredient'));
            $this->auditLogService->log(
                action: 'recipe_created',
                description: "Membuat resep baru untuk varian '{$variant->name}' ({$recipe->name}) dengan " . count($data['items']) . " bahan baku.",
                auditable: $recipe,
                newValues: [
                    'recipe_id' => $recipe->id,
                    'variant_id' => $variant->id,
                    'items_count' => count($data['items']),
                    'total_cost' => $calc['total_cost'],
                ],
                userId: $user?->id ?? auth()->id(),
                outletId: $variant->outlet_id,
                module: 'Recipe'
            );

            return $recipe->load(['menuVariant.menu', 'items.ingredient']);
        });
    }

    /**
     * Update an existing recipe.
     */
    public function update(Recipe $recipe, array $data, ?User $user = null): Recipe
    {
        $oldCalc = $this->calculateCost($recipe);

        return DB::transaction(function () use ($recipe, $data, $oldCalc, $user) {
            $recipe->update([
                'name' => $data['name'] ?? $recipe->name,
                'description' => $data['description'] ?? $recipe->description,
            ]);

            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items and recreate
                $recipe->items()->delete();

                $usedIngredients = [];
                foreach ($data['items'] as $itemData) {
                    if (in_array($itemData['ingredient_id'], $usedIngredients)) {
                        throw ValidationException::withMessages([
                            'items' => ['Bahan baku yang sama tidak boleh dimasukkan lebih dari satu kali.'],
                        ]);
                    }
                    $usedIngredients[] = $itemData['ingredient_id'];

                    $recipe->items()->create([
                        'ingredient_id' => $itemData['ingredient_id'],
                        'quantity' => $itemData['quantity'],
                        'unit' => $itemData['unit'],
                    ]);
                }
            }

            $newCalc = $this->calculateCost($recipe->load('items.ingredient'));

            $this->auditLogService->log(
                action: 'recipe_updated',
                description: "Memperbarui komposisi resep '{$recipe->name}'. Estimasi cost: Rp " . number_format($newCalc['total_cost'], 0, ',', '.'),
                auditable: $recipe,
                oldValues: [
                    'items_count' => count($oldCalc['items']),
                    'total_cost' => $oldCalc['total_cost'],
                ],
                newValues: [
                    'items_count' => count($newCalc['items']),
                    'total_cost' => $newCalc['total_cost'],
                ],
                userId: $user?->id ?? auth()->id(),
                outletId: $recipe->menuVariant?->outlet_id,
                module: 'Recipe'
            );

            return $recipe;
        });
    }

    /**
     * Delete recipe.
     */
    public function delete(Recipe $recipe, ?User $user = null): bool
    {
        $recipeName = $recipe->name;
        $variant = $recipe->menuVariant;

        return DB::transaction(function () use ($recipe, $recipeName, $variant, $user) {
            $recipe->items()->delete();
            $deleted = $recipe->delete();

            $this->auditLogService->log(
                action: 'recipe_deleted',
                description: "Menghapus resep '{$recipeName}' untuk varian " . ($variant?->name ?? '-'),
                auditable: null,
                oldValues: ['name' => $recipeName, 'variant_id' => $variant?->id],
                userId: $user?->id ?? auth()->id(),
                outletId: $variant?->outlet_id,
                module: 'Recipe'
            );

            return (bool) $deleted;
        });
    }
}
