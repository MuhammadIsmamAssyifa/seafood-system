<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CookingMethod;
use App\Models\Ingredient;
use App\Models\KitchenStation;
use App\Models\Menu;
use App\Models\Outlet;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\ProfitCalculationService;
use App\Services\ReportingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class DashboardWebController extends Controller
{
    public function __construct(
        protected ReportingService $reportingService,
        protected ProfitCalculationService $profitService,
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): View
    {
        $outlet = Outlet::first();
        $outletId = $outlet?->id ?? 1;

        $summary = $this->reportingService->getDashboardSummary($outletId);

        $salesReport = $this->reportingService->getSalesReport(
            $outletId,
            now()->subDays(6)->toDateString(),
            now()->toDateString()
        );

        return view('dashboard.index', compact('summary', 'salesReport', 'outlet'));
    }

    public function reports(Request $request): View
    {
        $outlet = Outlet::first();
        $outletId = $outlet?->id ?? 1;

        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $tab = $request->query('tab', 'sales');

        $salesReport = $this->reportingService->getSalesReport($outletId, $startDate, $endDate);
        $paymentReport = $this->reportingService->getPaymentReport($outletId, $startDate, $endDate);
        $menuReport = $this->reportingService->getMenuSalesReport($outletId, $startDate, $endDate);
        $profitReport = $this->profitService->getProfitSummary($outletId, $startDate, $endDate);
        $stockMovements = $this->reportingService->getStockMovementReport($outletId, null, null, $startDate, $endDate);

        return view('dashboard.reports', compact(
            'outlet',
            'startDate',
            'endDate',
            'tab',
            'salesReport',
            'paymentReport',
            'menuReport',
            'profitReport',
            'stockMovements'
        ));
    }

    public function inventory(Request $request): View
    {
        $outlet = Outlet::first();
        $outletId = $outlet?->id ?? 1;

        $inventoryReport = $this->reportingService->getInventoryReport($outletId);
        $ingredients = Ingredient::where('outlet_id', $outletId)->orderBy('name')->get();

        return view('dashboard.inventory', compact('outlet', 'inventoryReport', 'ingredients'));
    }

    public function storeStock(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'cost_per_unit' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $outlet = Outlet::first();
        $outletId = $outlet?->id ?? 1;

        $this->inventoryService->addStock([
            'outlet_id' => $outletId,
            'ingredient_id' => $validated['ingredient_id'],
            'quantity' => $validated['quantity'],
            'cost_per_unit' => $validated['cost_per_unit'],
            'note' => $validated['note'] ?? 'Restock Dapur Web Dashboard',
        ]);

        return redirect()->route('dashboard.inventory')->with('success', 'Stok bahan baku berhasil ditambahkan.');
    }

    public function menus(Request $request): View
    {
        $outlet = Outlet::first();
        $outletId = $outlet?->id ?? 1;

        $menus = Menu::where('outlet_id', $outletId)
            ->with(['variants.cookingMethod', 'variants.kitchenStation', 'variants.mainIngredient'])
            ->orderBy('name')
            ->get();

        $stations = KitchenStation::where('outlet_id', $outletId)->get();
        $cookingMethods = CookingMethod::where('outlet_id', $outletId)->get();
        $ingredients = Ingredient::where('outlet_id', $outletId)->get();

        return view('dashboard.menus', compact('outlet', 'menus', 'stations', 'cookingMethods', 'ingredients'));
    }

    public function users(Request $request): View
    {
        $users = User::with(['roles', 'outlets'])->orderBy('name')->get();
        $roles = Role::all();
        $outlets = Outlet::all();

        return view('dashboard.users', compact('users', 'roles', 'outlets'));
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'username' => ['required', 'string', 'unique:users,username', 'max:50'],
            'password' => ['required', 'string', 'min:6'],
            'pin' => ['nullable', 'string', 'size:4'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $outlet = Outlet::first();

        $user = User::create([
            'restaurant_id' => $outlet?->restaurant_id ?? 1,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'pin_hash' => ! empty($validated['pin']) ? Hash::make($validated['pin']) : null,
            'status' => 'active',
        ]);

        $user->assignRole($validated['role']);
        if ($outlet) {
            $user->outlets()->sync([$outlet->id]);
        }

        return redirect()->route('dashboard.users')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function auditLogs(Request $request): View
    {
        $outlet = Outlet::first();
        $outletId = $outlet?->id ?? 1;

        $logs = AuditLog::where('outlet_id', $outletId)
            ->orWhereNull('outlet_id')
            ->with(['user'])
            ->latest()
            ->paginate(20);

        return view('dashboard.audit_logs', compact('outlet', 'logs'));
    }
}
