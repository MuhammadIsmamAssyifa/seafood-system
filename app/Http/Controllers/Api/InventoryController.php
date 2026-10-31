<?php

namespace App\Http\Controllers\Api;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Get inventory list with current stock and overall summary
     */
    public function index(Request $request): JsonResponse
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id ?? 1;

        $query = Ingredient::query()
            ->where('outlet_id', $outletId)
            ->withSum('inventoryBatches as current_stock', 'remaining_quantity');

        if ($search = $request->query('search')) {
            $query->where('name', 'ILIKE', "%{$search}%");
        }

        $ingredients = $query->orderBy('name')->get()->map(function ($item) {
            $stock = round((float) ($item->current_stock ?? 0), 3);
            $minStock = 5.0; // default threshold

            $status = 'available';
            if ($stock <= 0) {
                $status = 'out_of_stock';
            } elseif ($stock <= $minStock) {
                $status = 'low_stock';
            }

            return [
                'id' => $item->id,
                'name' => $item->name,
                'unit' => $item->unit instanceof \BackedEnum ? $item->unit->value : $item->unit,
                'type' => $item->type,
                'current_stock' => $stock,
                'minimum_stock' => $minStock,
                'status' => $status,
            ];
        });

        // Filter by status if requested
        if ($statusFilter = $request->query('status')) {
            $ingredients = $ingredients->filter(fn ($i) => $i['status'] === $statusFilter)->values();
        }

        $totalIngredients = $ingredients->count();
        $lowStockCount = $ingredients->where('status', 'low_stock')->count();
        $outOfStockCount = $ingredients->where('status', 'out_of_stock')->count();
        $availableCount = $ingredients->where('status', 'available')->count();
        $totalStockQty = round((float) $ingredients->sum('current_stock'), 3);

        return response()->json([
            'data' => $ingredients,
            'summary' => [
                'total_ingredients' => $totalIngredients,
                'available_count' => $availableCount,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'total_stock_quantity' => $totalStockQty,
            ],
        ]);
    }

    /**
     * Get single ingredient detail with active batches and movement history
     */
    public function show(Request $request, Ingredient $ingredient): JsonResponse
    {
        $currentStock = $this->inventoryService->getAvailableStock($ingredient->id);

        $batches = InventoryBatch::where('ingredient_id', $ingredient->id)
            ->orderByDesc('received_at')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'quantity' => (float) $b->quantity,
                'remaining_quantity' => (float) $b->remaining_quantity,
                'cost_per_unit' => (float) $b->cost_per_unit,
                'total_value' => round((float) $b->remaining_quantity * (float) $b->cost_per_unit, 2),
                'source' => $b->source,
                'received_at' => $b->received_at,
                'is_active' => (float) $b->remaining_quantity > 0,
            ]);

        $recentMovements = StockMovement::where('ingredient_id', $ingredient->id)
            ->with('createdBy')
            ->latest()
            ->take(15)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'type' => $m->type instanceof \BackedEnum ? $m->type->value : $m->type,
                'quantity' => (float) $m->quantity,
                'cost_per_unit' => (float) $m->cost_per_unit,
                'total_cost' => (float) $m->total_cost,
                'note' => $m->note,
                'created_by' => $m->createdBy?->name,
                'created_at' => $m->created_at,
            ]);

        $status = 'available';
        if ($currentStock <= 0) {
            $status = 'out_of_stock';
        } elseif ($currentStock <= 5.0) {
            $status = 'low_stock';
        }

        return response()->json([
            'data' => [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'unit' => $ingredient->unit instanceof \BackedEnum ? $ingredient->unit->value : $ingredient->unit,
                'current_stock' => round($currentStock, 3),
                'status' => $status,
                'batches' => $batches,
                'recent_movements' => $recentMovements,
            ],
        ]);
    }

    /**
     * Stock adjustment (In/Out/Correction)
     */
    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'type' => ['required', 'in:in,out,adjustment'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'cost_per_unit' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id ?? 1;
        $ingredient = Ingredient::findOrFail($validated['ingredient_id']);
        $qty = (float) $validated['quantity'];
        $type = $validated['type'];

        if ($type === 'in') {
            $latestBatch = InventoryBatch::where('ingredient_id', $ingredient->id)->latest('received_at')->first();
            $cost = $validated['cost_per_unit'] ?? ($latestBatch ? (float) $latestBatch->cost_per_unit : 0);

            $batch = $this->inventoryService->addStock([
                'outlet_id' => $outletId,
                'ingredient_id' => $ingredient->id,
                'quantity' => $qty,
                'cost_per_unit' => $cost,
                'source' => 'Penyesuaian Manual',
                'note' => $validated['note'] ?? 'Penyesuaian stok masuk',
                'created_by' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Penyesuaian stok masuk berhasil disimpan.',
                'data' => [
                    'ingredient_id' => $ingredient->id,
                    'new_stock' => $this->inventoryService->getAvailableStock($ingredient->id),
                    'batch_id' => $batch->id,
                ],
            ]);
        } else {
            // Out or adjustment deduction
            $available = $this->inventoryService->getAvailableStock($ingredient->id);
            if ($available < $qty) {
                return response()->json([
                    'message' => 'Stok tidak mencukupi untuk pengurangan. Stok tersedia saat ini: ' . $available . ' ' . ($ingredient->unit instanceof \BackedEnum ? $ingredient->unit->value : $ingredient->unit),
                ], 422);
            }

            DB::transaction(function () use ($ingredient, $qty, $validated, $request, $outletId) {
                $batches = InventoryBatch::where('ingredient_id', $ingredient->id)
                    ->where('remaining_quantity', '>', 0)
                    ->orderBy('received_at')
                    ->lockForUpdate()
                    ->get();

                $remaining = $qty;
                foreach ($batches as $batch) {
                    if ($remaining <= 0) break;
                    $deduct = min((float) $batch->remaining_quantity, $remaining);
                    $batch->decrement('remaining_quantity', $deduct);
                    $cost = round($deduct * (float) $batch->cost_per_unit, 2);

                    StockMovement::create([
                        'outlet_id' => $outletId,
                        'ingredient_id' => $ingredient->id,
                        'inventory_batch_id' => $batch->id,
                        'type' => StockMovementType::Adjustment,
                        'quantity' => $deduct,
                        'cost_per_unit' => $batch->cost_per_unit,
                        'total_cost' => $cost,
                        'note' => $validated['note'] ?? 'Penyesuaian stok keluar / koreksi',
                        'created_by' => $request->user()->id,
                    ]);

                    $remaining -= $deduct;
                }
            });

            return response()->json([
                'message' => 'Penyesuaian stok keluar berhasil dicatat.',
                'data' => [
                    'ingredient_id' => $ingredient->id,
                    'new_stock' => $this->inventoryService->getAvailableStock($ingredient->id),
                ],
            ]);
        }
    }

    /**
     * Stock movements list with filters & pagination
     */
    public function movements(Request $request): JsonResponse
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id ?? 1;

        $query = StockMovement::query()
            ->where('outlet_id', $outletId)
            ->with(['ingredient', 'createdBy'])
            ->when($request->query('ingredient_id'), fn ($q, $id) => $q->where('ingredient_id', $id))
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type))
            ->when($request->query('start_date'), fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->query('end_date'), fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest();

        $paginated = $query->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
            ],
        ]);
    }

    /**
     * Cost Summary & Valuation across inventory
     */
    public function costSummary(Request $request): JsonResponse
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id ?? 1;

        $batches = InventoryBatch::where('outlet_id', $outletId)
            ->where('remaining_quantity', '>', 0)
            ->get();

        $totalStockValue = round((float) $batches->sum(fn ($b) => (float) $b->remaining_quantity * (float) $b->cost_per_unit), 2);
        $activeBatchesCount = $batches->count();

        // Ingredients with cost and valuation
        $ingredients = Ingredient::where('outlet_id', $outletId)
            ->with(['inventoryBatches' => fn ($q) => $q->where('remaining_quantity', '>', 0)])
            ->get()
            ->map(function ($item) {
                $active = $item->inventoryBatches;
                $remQty = round((float) $active->sum('remaining_quantity'), 3);
                $value = round((float) $active->sum(fn ($b) => (float) $b->remaining_quantity * (float) $b->cost_per_unit), 2);
                $avgCost = $remQty > 0 ? round($value / $remQty, 2) : 0.0;

                // Compare with previous cost to determine change %
                $latestBatch = $item->inventoryBatches()->latest('received_at')->first();
                $firstBatch = $item->inventoryBatches()->oldest('received_at')->first();
                $costChange = 0.0;
                if ($firstBatch && $latestBatch && (float) $firstBatch->cost_per_unit > 0) {
                    $costChange = round((((float) $latestBatch->cost_per_unit - (float) $firstBatch->cost_per_unit) / (float) $firstBatch->cost_per_unit) * 100, 1);
                }

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'unit' => $item->unit instanceof \BackedEnum ? $item->unit->value : $item->unit,
                    'current_stock' => $remQty,
                    'average_cost' => $avgCost,
                    'total_value' => $value,
                    'cost_change' => $costChange,
                    'batches_count' => $active->count(),
                ];
            });

        return response()->json([
            'data' => [
                'total_stock_value' => $totalStockValue,
                'ingredient_count' => $ingredients->count(),
                'active_batches_count' => $activeBatchesCount,
                'ingredients' => $ingredients,
            ],
        ]);
    }

    /**
     * Detailed Batch Cost and FIFO queue for specific ingredient
     */
    public function costDetail(Request $request, Ingredient $ingredient): JsonResponse
    {
        $batches = InventoryBatch::where('ingredient_id', $ingredient->id)
            ->orderBy('received_at')
            ->get();

        $activeBatches = $batches->where('remaining_quantity', '>', 0);
        $totalStock = round((float) $activeBatches->sum('remaining_quantity'), 3);
        $totalStockValue = round((float) $activeBatches->sum(fn ($b) => (float) $b->remaining_quantity * (float) $b->cost_per_unit), 2);
        $averageCost = $totalStock > 0 ? round($totalStockValue / $totalStock, 2) : 0.0;

        $batchList = $batches->map(fn ($b, $idx) => [
            'id' => $b->id,
            'batch_number' => 'BATCH-' . str_pad($b->id, 4, '0', STR_PAD_LEFT),
            'received_at' => $b->received_at,
            'quantity' => (float) $b->quantity,
            'remaining_quantity' => (float) $b->remaining_quantity,
            'cost_per_unit' => (float) $b->cost_per_unit,
            'value' => round((float) $b->remaining_quantity * (float) $b->cost_per_unit, 2),
            'source' => $b->source ?? 'Pembelian Supplier',
            'is_depleted' => (float) $b->remaining_quantity <= 0,
            'fifo_order' => (float) $b->remaining_quantity > 0 ? $idx + 1 : null,
        ])->values();

        // Price trend across batches
        $costHistory = $batches->map(fn ($b) => [
            'date' => $b->received_at?->toDateString(),
            'cost_per_unit' => (float) $b->cost_per_unit,
            'quantity' => (float) $b->quantity,
        ])->values();

        return response()->json([
            'data' => [
                'ingredient' => [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'unit' => $ingredient->unit instanceof \BackedEnum ? $ingredient->unit->value : $ingredient->unit,
                ],
                'stock' => $totalStock,
                'total_stock_value' => $totalStockValue,
                'average_cost' => $averageCost,
                'batches' => $batchList,
                'cost_history' => $costHistory,
            ],
        ]);
    }
}

