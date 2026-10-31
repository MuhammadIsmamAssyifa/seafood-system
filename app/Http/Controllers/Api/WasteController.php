<?php

namespace App\Http\Controllers\Api;

use App\Enums\StockMovementType;
use App\Enums\WasteReason;
use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\InventoryBatch;
use App\Models\StockMovement;
use App\Models\WasteRecord;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WasteController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id;

        $query = WasteRecord::query()
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->with(['ingredient', 'creator', 'outlet'])
            ->latest();

        if ($request->filled('ingredient_id')) {
            $query->where('ingredient_id', $request->query('ingredient_id'));
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->query('reason'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->query('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->query('end_date'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->whereHas('ingredient', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%");
            });
        }

        $perPage = (int) $request->query('per_page', 15);
        $wastes = $query->paginate($perPage);

        return response()->json([
            'data' => $wastes->items(),
            'meta' => [
                'current_page' => $wastes->currentPage(),
                'last_page' => $wastes->lastPage(),
                'per_page' => $wastes->perPage(),
                'total' => $wastes->total(),
            ],
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id;

        $query = WasteRecord::query()
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId));

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->query('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->query('end_date'));
        }

        $records = $query->with('ingredient')->get();

        $totalCost = (float) $records->sum('total_cost');
        $totalQuantity = (float) $records->sum('quantity');
        $totalCount = $records->count();

        // Most wasted ingredient
        $groupedByIngredient = $records->groupBy('ingredient_id')->map(function ($items) {
            $first = $items->first();
            return [
                'ingredient_id' => $first->ingredient_id,
                'ingredient_name' => $first->ingredient?->name ?? 'Unknown',
                'unit' => $first->ingredient?->unit->value ?? 'kg',
                'total_cost' => (float) $items->sum('total_cost'),
                'total_quantity' => (float) $items->sum('quantity'),
                'count' => $items->count(),
            ];
        })->sortByDesc('total_cost')->values();

        $mostWasted = $groupedByIngredient->first() ?? null;

        // Reason breakdown
        $reasonBreakdown = $records->groupBy(fn ($item) => $item->reason->value ?? (string) $item->reason)
            ->map(function ($items, $reasonKey) use ($totalCost) {
                $cost = (float) $items->sum('total_cost');
                $qty = (float) $items->sum('quantity');
                $percentage = $totalCost > 0 ? round(($cost / $totalCost) * 100, 1) : 0;
                $enumObj = WasteReason::tryFrom($reasonKey);

                return [
                    'reason' => $reasonKey,
                    'label' => $enumObj ? $enumObj->label() : ucfirst($reasonKey),
                    'count' => $items->count(),
                    'total_cost' => $cost,
                    'total_quantity' => $qty,
                    'percentage' => $percentage,
                ];
            })->values();

        // Total active inventory value to calculate waste percentage
        $totalInventoryValue = (float) InventoryBatch::query()
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->where('remaining_quantity', '>', 0)
            ->sum(DB::raw('remaining_quantity * cost_per_unit'));

        $wastePercentage = ($totalInventoryValue + $totalCost) > 0
            ? round(($totalCost / ($totalInventoryValue + $totalCost)) * 100, 2)
            : 0;

        return response()->json([
            'total_cost' => $totalCost,
            'total_quantity' => $totalQuantity,
            'total_records' => $totalCount,
            'waste_percentage' => $wastePercentage,
            'inventory_value' => $totalInventoryValue,
            'most_wasted_ingredient' => $mostWasted,
            'top_wasted_ingredients' => $groupedByIngredient->take(5),
            'reason_breakdown' => $reasonBreakdown,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $outlet = $request->user()->outlets()->first();
        $outletId = $outlet?->id;

        $record = WasteRecord::query()
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->with(['ingredient', 'creator', 'outlet', 'stockMovements.inventoryBatch'])
            ->find($id);

        if (! $record) {
            return response()->json([
                'message' => 'Catatan waste tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $record->id,
                'outlet_id' => $record->outlet_id,
                'ingredient_id' => $record->ingredient_id,
                'ingredient_name' => $record->ingredient?->name,
                'unit' => $record->ingredient?->unit->value ?? 'kg',
                'quantity' => (float) $record->quantity,
                'unit_cost' => (float) $record->unit_cost,
                'total_cost' => (float) $record->total_cost,
                'reason' => $record->reason->value ?? (string) $record->reason,
                'reason_label' => $record->reason instanceof WasteReason ? $record->reason->label() : ucfirst((string) $record->reason),
                'note' => $record->note,
                'creator_name' => $record->creator?->name ?? 'Admin',
                'created_at' => $record->created_at->toISOString(),
                'stock_movements' => $record->stockMovements->map(fn ($m) => [
                    'id' => $m->id,
                    'batch_id' => $m->inventory_batch_id,
                    'quantity' => (float) $m->quantity,
                    'cost_per_unit' => (float) $m->cost_per_unit,
                    'total_cost' => (float) $m->total_cost,
                    'created_at' => $m->created_at->toISOString(),
                ]),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'reason' => ['required', 'string', Rule::in(['expired', 'damaged', 'cooking_loss', 'wrong_preparation', 'other'])],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $outlet = $user->outlets()->first();
        $requestedQty = (float) $validated['quantity'];

        $ingredient = Ingredient::findOrFail($validated['ingredient_id']);
        $outletId = $outlet?->id ?? $ingredient->outlet_id;

        return DB::transaction(function () use ($validated, $user, $outletId, $ingredient, $requestedQty) {
            // Check available stock
            $availableStock = (float) InventoryBatch::query()
                ->where('ingredient_id', $ingredient->id)
                ->where('remaining_quantity', '>', 0)
                ->sum('remaining_quantity');

            if ($requestedQty > $availableStock) {
                return response()->json([
                    'message' => 'Stok bahan tidak mencukupi.',
                    'errors' => [
                        'quantity' => [
                            "Stok bahan tidak mencukupi. Stok tersedia: {$availableStock} {$ingredient->unit->value}.",
                        ],
                    ],
                ], 422);
            }

            // Deduct via FIFO
            $batches = InventoryBatch::query()
                ->where('ingredient_id', $ingredient->id)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('received_at', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            $remainingToDeduct = $requestedQty;
            $totalCost = 0;
            $deductions = [];

            foreach ($batches as $batch) {
                if ($remainingToDeduct <= 0) {
                    break;
                }

                $batchRemaining = (float) $batch->remaining_quantity;
                $deductAmount = min($batchRemaining, $remainingToDeduct);
                $batchCostPerUnit = (float) $batch->cost_per_unit;
                $costForThisDeduction = round($deductAmount * $batchCostPerUnit, 2);

                $batch->decrement('remaining_quantity', $deductAmount);

                $totalCost += $costForThisDeduction;
                $remainingToDeduct -= $deductAmount;

                $deductions[] = [
                    'batch' => $batch,
                    'deduct' => $deductAmount,
                    'cost_per_unit' => $batchCostPerUnit,
                    'total_cost' => $costForThisDeduction,
                ];
            }

            $unitCost = $requestedQty > 0 ? round($totalCost / $requestedQty, 2) : 0;

            // Create WasteRecord
            $wasteRecord = WasteRecord::create([
                'outlet_id' => $outletId,
                'ingredient_id' => $ingredient->id,
                'quantity' => $requestedQty,
                'unit_cost' => $unitCost,
                'total_cost' => round($totalCost, 2),
                'reason' => $validated['reason'],
                'note' => $validated['note'] ?? null,
                'created_by' => $user->id,
            ]);

            // Create StockMovements
            foreach ($deductions as $d) {
                StockMovement::create([
                    'outlet_id' => $outletId,
                    'ingredient_id' => $ingredient->id,
                    'inventory_batch_id' => $d['batch']->id,
                    'type' => StockMovementType::Waste,
                    'quantity' => -$d['deduct'],
                    'cost_per_unit' => $d['cost_per_unit'],
                    'total_cost' => $d['total_cost'],
                    'reference_type' => WasteRecord::class,
                    'reference_id' => $wasteRecord->id,
                    'note' => "Waste [{$wasteRecord->reason->label()}]: " . ($wasteRecord->note ?? '-'),
                    'created_by' => $user->id,
                ]);
            }

            // Audit Log
            $unitStr = $ingredient->unit->value ?? 'kg';
            $formattedTotal = number_format($wasteRecord->total_cost, 0, ',', '.');
            $this->auditLogService->log(
                action: 'waste_created',
                description: "Mencatat waste {$ingredient->name} sebanyak {$requestedQty} {$unitStr} senilai Rp {$formattedTotal}. Alasan: {$wasteRecord->reason->label()}.",
                auditable: $wasteRecord,
                newValues: [
                    'ingredient' => $ingredient->name,
                    'quantity' => $requestedQty,
                    'unit_cost' => $unitCost,
                    'total_cost' => (float) $wasteRecord->total_cost,
                    'reason' => $wasteRecord->reason->value,
                    'note' => $wasteRecord->note,
                ],
                userId: $user->id,
                outletId: $outletId
            );

            return response()->json([
                'message' => 'Pencatatan waste berhasil disimpan.',
                'data' => [
                    'id' => $wasteRecord->id,
                    'ingredient_id' => $wasteRecord->ingredient_id,
                    'quantity' => (float) $wasteRecord->quantity,
                    'unit_cost' => (float) $wasteRecord->unit_cost,
                    'total_cost' => (float) $wasteRecord->total_cost,
                    'reason' => $wasteRecord->reason->value,
                    'reason_label' => $wasteRecord->reason->label(),
                    'note' => $wasteRecord->note,
                    'created_at' => $wasteRecord->created_at->toISOString(),
                ],
            ], 201);
        });
    }
}

