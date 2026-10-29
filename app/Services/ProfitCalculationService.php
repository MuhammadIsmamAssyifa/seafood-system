<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ProfitCalculationService
{
    /**
     * Get detailed profit and COGS for a single order.
     */
    public function getOrderProfit(Order $order): array
    {
        $order->load(['items.menuVariant', 'orderTable']);

        $revenue = (float) $order->grand_total;
        $totalCogs = (float) $order->total_cogs;
        $grossProfit = (float) $order->gross_profit;
        $marginPercentage = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0.0;

        /** @var Collection<int, OrderItem> $orderItems */
        $orderItems = $order->items;

        $itemsBreakdown = $orderItems->map(function ($item) {
            $itemRevenue = (float) $item->subtotal;
            $itemCogs = (float) $item->cogs;
            $itemProfit = (float) $item->gross_profit;
            $itemMargin = $itemRevenue > 0 ? round(($itemProfit / $itemRevenue) * 100, 2) : 0.0;

            return [
                'order_item_id' => $item->id,
                'menu_name' => $item->menu_name_snapshot,
                'variant_name' => $item->variant_name_snapshot,
                'quantity' => (float) $item->quantity,
                'sales_unit' => $item->sales_unit instanceof \BackedEnum ? $item->sales_unit->value : $item->sales_unit,
                'price' => (float) $item->price_snapshot,
                'revenue' => $itemRevenue,
                'cogs' => $itemCogs,
                'gross_profit' => $itemProfit,
                'margin_percentage' => $itemMargin,
            ];
        });

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'table_number' => $order->orderTable?->number,
            'status' => $order->status instanceof \BackedEnum ? $order->status->value : $order->status,
            'subtotal' => (float) $order->subtotal,
            'discount' => (float) $order->discount,
            'revenue' => $revenue,
            'total_cogs' => $totalCogs,
            'gross_profit' => $grossProfit,
            'margin_percentage' => $marginPercentage,
            'items' => $itemsBreakdown,
            'created_at' => $order->created_at,
        ];
    }

    /**
     * Get aggregated profit and COGS summary for an outlet.
     */
    public function getProfitSummary(
        int $outletId,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = Order::query()
            ->where('outlet_id', $outletId)
            ->where('status', OrderStatus::Completed);

        if ($startDate) {
            $query->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
        }

        if ($endDate) {
            $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        $orders = $query->with('items')->get();

        $totalOrders = $orders->count();
        $totalRevenue = round((float) $orders->sum('grand_total'), 2);
        $totalCogs = round((float) $orders->sum('total_cogs'), 2);
        $totalGrossProfit = round((float) $orders->sum('gross_profit'), 2);
        $marginPercentage = $totalRevenue > 0
            ? round(($totalGrossProfit / $totalRevenue) * 100, 2)
            : 0.0;

        // Breakdown per menu variant from completed orders
        $items = $orders->flatMap->items;
        $menuBreakdown = $items->groupBy('menu_variant_id')->map(function ($groupedItems) {
            $first = $groupedItems->first();
            $qtySold = (float) $groupedItems->sum('quantity');
            $revenue = round((float) $groupedItems->sum('subtotal'), 2);
            $cogs = round((float) $groupedItems->sum('cogs'), 2);
            $profit = round((float) $groupedItems->sum('gross_profit'), 2);
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0.0;

            return [
                'menu_variant_id' => $first->menu_variant_id,
                'menu_name' => $first->menu_name_snapshot,
                'variant_name' => $first->variant_name_snapshot,
                'sales_unit' => $first->sales_unit instanceof \BackedEnum ? $first->sales_unit->value : $first->sales_unit,
                'quantity_sold' => $qtySold,
                'total_revenue' => $revenue,
                'total_cogs' => $cogs,
                'gross_profit' => $profit,
                'margin_percentage' => $margin,
            ];
        })->values();

        return [
            'outlet_id' => $outletId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'total_cogs' => $totalCogs,
            'total_gross_profit' => $totalGrossProfit,
            'margin_percentage' => $marginPercentage,
            'menu_breakdown' => $menuBreakdown,
        ];
    }
}
