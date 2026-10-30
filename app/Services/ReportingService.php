<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Enums\TableStatus;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTable;
use App\Models\Payment;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;

class ReportingService
{
    /**
     * Dashboard High-Level Summary
     */
    public function getDashboardSummary(int $outletId, ?string $date = null): array
    {
        $targetDate = $date ? Carbon::parse($date) : Carbon::today();
        $startOfDay = $targetDate->copy()->startOfDay();
        $endOfDay = $targetDate->copy()->endOfDay();

        // 1. Sales & Profit Today (SQL Aggregation for High Performance)
        $completedMetrics = Order::query()
            ->where('outlet_id', $outletId)
            ->where('status', OrderStatus::Completed)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->selectRaw('COALESCE(SUM(grand_total), 0) as revenue, COALESCE(SUM(total_cogs), 0) as cogs, COALESCE(SUM(gross_profit), 0) as profit, COUNT(*) as completed_count')
            ->first();

        $todayRevenue = round((float) ($completedMetrics->revenue ?? 0), 2);
        $todayCogs = round((float) ($completedMetrics->cogs ?? 0), 2);
        $todayGrossProfit = round((float) ($completedMetrics->profit ?? 0), 2);
        $todayProfitMargin = $todayRevenue > 0
            ? round(($todayGrossProfit / $todayRevenue) * 100, 2)
            : 0.0;
        $completedOrdersCount = (int) ($completedMetrics->completed_count ?? 0);
        $todayAvgTransaction = ($todayRevenue > 0 && $completedOrdersCount > 0)
            ? round($todayRevenue / $completedOrdersCount, 2)
            : 0.0;

        // 2. Orders Metrics
        $totalOrdersMetrics = Order::query()
            ->where('outlet_id', $outletId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->selectRaw('COUNT(*) as total_orders, COALESCE(SUM(guest_count), 0) as total_guests')
            ->first();

        $totalOrdersToday = (int) ($totalOrdersMetrics->total_orders ?? 0);
        $guestCountToday = (int) ($totalOrdersMetrics->total_guests ?? 0);

        $activeOrdersCount = Order::query()
            ->where('outlet_id', $outletId)
            ->whereIn('status', [
                OrderStatus::Draft,
                OrderStatus::Submitted,
                OrderStatus::Preparing,
                OrderStatus::Ready,
            ])
            ->count();

        $statusCounts = Order::query()
            ->where('outlet_id', $outletId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        // 2b. Payments Today
        $paymentsToday = Payment::query()
            ->whereHas('order', fn ($q) => $q->where('outlet_id', $outletId))
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [$startOfDay, $endOfDay])
            ->get();

        $todayCash = round((float) $paymentsToday->filter(fn ($p) => ($p->method instanceof \BackedEnum ? $p->method->value : $p->method) === 'cash')->sum('amount'), 2);
        $todayTransfer = round((float) $paymentsToday->filter(fn ($p) => ($p->method instanceof \BackedEnum ? $p->method->value : $p->method) === 'transfer')->sum('amount'), 2);
        $todayQris = round((float) $paymentsToday->filter(fn ($p) => ($p->method instanceof \BackedEnum ? $p->method->value : $p->method) === 'qris')->sum('amount'), 2);
        $todayCollected = round((float) $paymentsToday->sum('amount'), 2);

        // 2c. Top Selling Today
        $topSellingToday = OrderItem::query()
            ->whereHas('orderBatch.order', function ($q) use ($outletId, $startOfDay, $endOfDay) {
                $q->where('outlet_id', $outletId)
                    ->where('status', OrderStatus::Completed)
                    ->whereBetween('created_at', [$startOfDay, $endOfDay]);
            })
            ->selectRaw('menu_variant_id, menu_name_snapshot, variant_name_snapshot, sales_unit, SUM(quantity) as total_qty, SUM(subtotal) as total_rev')
            ->groupBy('menu_variant_id', 'menu_name_snapshot', 'variant_name_snapshot', 'sales_unit')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get()
            ->map(fn ($i) => [
                'variant_id' => $i->menu_variant_id,
                'menu_name' => $i->menu_name_snapshot,
                'variant_name' => $i->variant_name_snapshot,
                'sales_unit' => $i->sales_unit instanceof \BackedEnum ? $i->sales_unit->value : $i->sales_unit,
                'quantity_sold' => (float) $i->total_qty,
                'total_revenue' => (float) $i->total_rev,
            ])
            ->values();

        // 3. Table Occupancy
        $totalTables = OrderTable::where('outlet_id', $outletId)->count();
        $occupiedTables = OrderTable::where('outlet_id', $outletId)
            ->where('status', TableStatus::Occupied)
            ->count();
        $availableTables = max(0, $totalTables - $occupiedTables);
        $occupancyRate = $totalTables > 0
            ? round(($occupiedTables / $totalTables) * 100, 2)
            : 0.0;

        // 4. Low Stock Alerts (Stock <= 5)
        $ingredients = Ingredient::where('outlet_id', $outletId)
            ->withSum('inventoryBatches as total_stock', 'remaining_quantity')
            ->get();

        $lowStockItems = $ingredients->filter(function ($item) {
            return (float) ($item->total_stock ?? 0) <= 5.0;
        })->map(function ($item) {
            $unit = $item->unit instanceof \BackedEnum ? $item->unit->value : $item->unit;

            return [
                'ingredient_id' => $item->id,
                'name' => $item->name,
                'current_stock' => (float) ($item->total_stock ?? 0),
                'unit' => $unit,
            ];
        })->values();

        // 5. Recent 5 Orders
        $recentOrders = Order::where('outlet_id', $outletId)
            ->with(['orderTable', 'createdBy'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'table_number' => $order->orderTable?->number,
                    'customer_name' => $order->customer_name,
                    'status' => $order->status instanceof \BackedEnum ? $order->status->value : $order->status,
                    'grand_total' => (float) $order->grand_total,
                    'created_at' => $order->created_at,
                ];
            });

        return [
            'outlet_id' => $outletId,
            'date' => $targetDate->toDateString(),
            'sales' => [
                'today_revenue' => $todayRevenue,
                'today_cogs' => $todayCogs,
                'today_gross_profit' => $todayGrossProfit,
                'today_profit_margin' => $todayProfitMargin,
                'average_transaction' => $todayAvgTransaction,
            ],
            'orders' => [
                'total_today' => $totalOrdersToday,
                'completed_today' => $completedOrdersCount,
                'active_orders' => $activeOrdersCount,
                'guests_today' => $guestCountToday,
                'status_breakdown' => [
                    'draft' => (int) ($statusCounts['draft'] ?? 0),
                    'submitted' => (int) ($statusCounts['submitted'] ?? 0),
                    'preparing' => (int) ($statusCounts['preparing'] ?? 0),
                    'ready' => (int) ($statusCounts['ready'] ?? 0),
                    'completed' => (int) ($statusCounts['completed'] ?? 0),
                    'cancelled' => (int) ($statusCounts['cancelled'] ?? 0),
                ],
            ],
            'payments' => [
                'today_cash' => $todayCash,
                'today_transfer' => $todayTransfer,
                'today_qris' => $todayQris,
                'total_collected' => $todayCollected,
            ],
            'tables' => [
                'total_tables' => $totalTables,
                'occupied_tables' => $occupiedTables,
                'available_tables' => $availableTables,
                'occupancy_rate' => $occupancyRate,
            ],
            'inventory_alerts' => [
                'low_stock_count' => $lowStockItems->count(),
                'low_stock_items' => $lowStockItems,
            ],
            'recent_orders' => $recentOrders,
            'top_selling_menus' => $topSellingToday,
        ];
    }

    /**
     * Sales Report with Daily Trend
     */
    public function getSalesReport(int $outletId, ?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::today()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::today()->endOfDay();

        $orders = Order::query()
            ->where('outlet_id', $outletId)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', OrderStatus::Completed)
            ->orderBy('created_at')
            ->get();

        $totalOrders = $orders->count();
        $totalGuests = (int) $orders->sum('guest_count');
        $grossSales = round((float) $orders->sum('subtotal'), 2);
        $totalDiscounts = round((float) $orders->sum('discount'), 2);
        $netSales = round((float) $orders->sum('grand_total'), 2);

        $aov = $totalOrders > 0 ? round($netSales / $totalOrders, 2) : 0.0;
        $spendPerGuest = $totalGuests > 0 ? round($netSales / $totalGuests, 2) : 0.0;

        // Daily Trend breakdown
        $dailyTrends = $orders->groupBy(fn ($order) => $order->created_at->toDateString())
            ->map(function ($dayOrders, $date) {
                $count = $dayOrders->count();
                $gross = round((float) $dayOrders->sum('subtotal'), 2);
                $discount = round((float) $dayOrders->sum('discount'), 2);
                $net = round((float) $dayOrders->sum('grand_total'), 2);

                return [
                    'date' => $date,
                    'total_orders' => $count,
                    'gross_sales' => $gross,
                    'discounts' => $discount,
                    'net_sales' => $net,
                ];
            })->values();

        return [
            'outlet_id' => $outletId,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'summary' => [
                'total_orders' => $totalOrders,
                'total_guests' => $totalGuests,
                'gross_sales' => $grossSales,
                'total_discounts' => $totalDiscounts,
                'net_sales' => $netSales,
                'average_order_value' => $aov,
                'average_spend_per_guest' => $spendPerGuest,
            ],
            'daily_trends' => $dailyTrends,
        ];
    }

    /**
     * Payment Report
     */
    public function getPaymentReport(int $outletId, ?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::today()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::today()->endOfDay();

        $payments = Payment::query()
            ->whereHas('order', fn ($q) => $q->where('outlet_id', $outletId))
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [$start, $end])
            ->with(['order.orderTable', 'createdBy'])
            ->orderBy('paid_at')
            ->get();

        $totalCollected = round((float) $payments->sum('amount'), 2);
        $totalTransactions = $payments->count();

        // Method Breakdown
        $methodBreakdown = $payments->groupBy(function ($p) {
            return $p->method instanceof \BackedEnum ? $p->method->value : $p->method;
        })->map(function ($group, $method) use ($totalCollected) {
            $amount = round((float) $group->sum('amount'), 2);
            $count = $group->count();
            $percentage = $totalCollected > 0 ? round(($amount / $totalCollected) * 100, 2) : 0.0;

            return [
                'method' => $method,
                'total_amount' => $amount,
                'transaction_count' => $count,
                'percentage' => $percentage,
            ];
        })->values();

        // List Transactions
        $transactions = $payments->map(function ($p) {
            $method = $p->method instanceof \BackedEnum ? $p->method->value : $p->method;

            return [
                'id' => $p->id,
                'order_id' => $p->order_id,
                'order_number' => $p->order?->order_number,
                'table_number' => $p->order?->orderTable?->number,
                'method' => $method,
                'amount' => (float) $p->amount,
                'paid_at' => $p->paid_at,
                'cashier_name' => $p->createdBy?->name,
            ];
        });

        return [
            'outlet_id' => $outletId,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'summary' => [
                'total_collected' => $totalCollected,
                'total_transactions' => $totalTransactions,
            ],
            'method_breakdown' => $methodBreakdown,
            'transactions' => $transactions,
        ];
    }

    /**
     * Menu Sales & Best Seller Report
     */
    public function getMenuSalesReport(int $outletId, ?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::today()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::today()->endOfDay();

        $items = OrderItem::query()
            ->whereHas('orderBatch.order', function ($q) use ($outletId, $start, $end) {
                $q->where('outlet_id', $outletId)
                    ->where('status', OrderStatus::Completed)
                    ->whereBetween('created_at', [$start, $end]);
            })
            ->with('menuVariant.menu')
            ->get();

        $totalItemsSold = (float) $items->sum('quantity');
        $totalRevenue = round((float) $items->sum('subtotal'), 2);

        $menuRankings = $items->groupBy('menu_variant_id')->map(function ($group) use ($totalRevenue) {
            $first = $group->first();
            $menu = $first->menuVariant?->menu;
            $qty = (float) $group->sum('quantity');
            $revenue = round((float) $group->sum('subtotal'), 2);
            $cogs = round((float) $group->sum('cogs'), 2);
            $profit = round((float) $group->sum('gross_profit'), 2);
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0.0;
            $revenueShare = $totalRevenue > 0 ? round(($revenue / $totalRevenue) * 100, 2) : 0.0;
            $salesUnit = $first->sales_unit instanceof \BackedEnum ? $first->sales_unit->value : $first->sales_unit;
            $menuType = $menu?->type instanceof \BackedEnum ? $menu->type->value : $menu?->type;

            return [
                'menu_variant_id' => $first->menu_variant_id,
                'menu_name' => $first->menu_name_snapshot,
                'variant_name' => $first->variant_name_snapshot,
                'menu_type' => $menuType,
                'sales_unit' => $salesUnit,
                'quantity_sold' => $qty,
                'total_revenue' => $revenue,
                'total_cogs' => $cogs,
                'gross_profit' => $profit,
                'margin_percentage' => $margin,
                'revenue_percentage' => $revenueShare,
            ];
        })->sortByDesc('total_revenue')->values();

        return [
            'outlet_id' => $outletId,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'summary' => [
                'total_items_sold' => $totalItemsSold,
                'total_revenue' => $totalRevenue,
            ],
            'menu_rankings' => $menuRankings,
        ];
    }

    /**
     * Inventory Stock & Valuation Report
     */
    public function getInventoryReport(int $outletId): array
    {
        $ingredients = Ingredient::where('outlet_id', $outletId)
            ->with(['inventoryBatches' => function ($q) {
                $q->where('remaining_quantity', '>', 0);
            }])
            ->get();

        $totalValuation = 0.0;
        $lowStockCount = 0;

        $items = $ingredients->map(function ($ing) use (&$totalValuation, &$lowStockCount) {
            $batches = $ing->inventoryBatches;
            $stock = (float) $batches->sum('remaining_quantity');
            $valuation = round((float) $batches->sum(fn ($b) => (float) $b->remaining_quantity * (float) $b->cost_per_unit), 2);
            $totalValuation += $valuation;

            $isLowStock = $stock <= 5.0;
            if ($isLowStock) {
                $lowStockCount++;
            }

            $unit = $ing->unit instanceof \BackedEnum ? $ing->unit->value : $ing->unit;

            return [
                'id' => $ing->id,
                'name' => $ing->name,
                'unit' => $unit,
                'type' => $ing->type,
                'current_stock' => $stock,
                'valuation' => $valuation,
                'active_batches_count' => $batches->count(),
                'is_low_stock' => $isLowStock,
            ];
        });

        return [
            'outlet_id' => $outletId,
            'summary' => [
                'total_ingredients' => $ingredients->count(),
                'total_valuation' => round($totalValuation, 2),
                'low_stock_count' => $lowStockCount,
            ],
            'ingredients' => $items,
        ];
    }

    /**
     * Stock Movement Audit Trail Report
     */
    public function getStockMovementReport(
        int $outletId,
        ?int $ingredientId = null,
        ?string $type = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = StockMovement::where('outlet_id', $outletId)
            ->with(['ingredient', 'createdBy']);

        if ($ingredientId) {
            $query->where('ingredient_id', $ingredientId);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($startDate) {
            $query->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
        }

        if ($endDate) {
            $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        $movements = $query->latest()->get();

        $totalIn = (float) $movements->where('type', StockMovementType::In)->sum('quantity');
        $totalSale = (float) $movements->where('type', StockMovementType::Sale)->sum('quantity');
        $totalWaste = (float) $movements->where('type', StockMovementType::Waste)->sum('quantity');
        $totalAdjustment = (float) $movements->where('type', StockMovementType::Adjustment)->sum('quantity');

        $list = $movements->map(function ($m) {
            $typeVal = $m->type instanceof \BackedEnum ? $m->type->value : $m->type;
            $unitVal = $m->ingredient?->unit instanceof \BackedEnum ? $m->ingredient->unit->value : $m->ingredient?->unit;

            return [
                'id' => $m->id,
                'ingredient_id' => $m->ingredient_id,
                'ingredient_name' => $m->ingredient?->name,
                'inventory_batch_id' => $m->inventory_batch_id,
                'type' => $typeVal,
                'quantity' => (float) $m->quantity,
                'unit' => $unitVal,
                'cost_per_unit' => (float) $m->cost_per_unit,
                'total_cost' => (float) $m->total_cost,
                'note' => $m->note,
                'created_by' => $m->createdBy?->name,
                'created_at' => $m->created_at,
            ];
        });

        return [
            'outlet_id' => $outletId,
            'summary' => [
                'total_movements' => $movements->count(),
                'total_in' => $totalIn,
                'total_sale' => $totalSale,
                'total_waste' => $totalWaste,
                'total_adjustment' => $totalAdjustment,
            ],
            'movements' => $list,
        ];
    }
}
