<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KitchenActionController;
use App\Http\Controllers\Api\KitchenController;
use App\Http\Controllers\Api\OrderBatchController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderItemChangeController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfitReportController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TableController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return new UserResource($request->user());
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/staff-login', [AuthController::class, 'staffLogin'])->middleware('throttle:login');
});

Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    // Dashboard Summary
    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])
        ->middleware('permission:dashboard.view');

    // Tables
    Route::prefix('tables')->group(function () {
        Route::get('/', [TableController::class, 'index'])
            ->middleware('permission:order.view|order.create');
        Route::get('/{table}', [TableController::class, 'show'])
            ->middleware('permission:order.view|order.create');
    });

    // Catalog
    Route::prefix('catalog')->group(function () {
        Route::get('/menus', [CatalogController::class, 'menus'])
            ->middleware('permission:menu.view');
        Route::get('/stations', [CatalogController::class, 'stations'])
            ->middleware('permission:menu.view|kitchen.view');
    });

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])
            ->middleware('permission:order.view');
        Route::post('/', [OrderController::class, 'store'])
            ->middleware('permission:order.create');
        Route::get('/{order}', [OrderController::class, 'show'])
            ->middleware('permission:order.view');
        Route::post('/{order}/items', [OrderItemController::class, 'store'])
            ->middleware('permission:order.create|order.update');
        Route::get('/{order}/payments', [PaymentController::class, 'index'])
            ->middleware('permission:order.view|payment.view');
        Route::post('/{order}/payments', [PaymentController::class, 'store'])
            ->middleware('permission:payment.create');
        Route::get('/{order}/payments/summary', [PaymentController::class, 'summary'])
            ->middleware('permission:order.view|payment.view');
        Route::get('/{order}/payment-summary', [PaymentController::class, 'summary'])
            ->middleware('permission:order.view|payment.view');
        Route::get('/{order}/adjustments', [OrderItemChangeController::class, 'forOrder'])
            ->middleware('permission:order.view|order.change');
    });

    // Order Item Changes (Customer Approval / Rejection)
    Route::prefix('order-item-changes')->group(function () {
        Route::get('/', [OrderItemChangeController::class, 'index'])
            ->middleware('permission:order.view|order.change');
        Route::post('/', [OrderItemChangeController::class, 'store'])
            ->middleware('permission:order.change');
        Route::patch('/{change}/approve', [OrderItemChangeController::class, 'approve'])
            ->middleware('permission:order.change|order.update');
        Route::patch('/{change}/reject', [OrderItemChangeController::class, 'reject'])
            ->middleware('permission:order.change|order.update');
    });

    // Order Items
    Route::prefix('order-items')->group(function () {
        Route::patch('/{orderItem}', [OrderItemController::class, 'update'])
            ->middleware('permission:order.update');
        Route::delete('/{orderItem}', [OrderItemController::class, 'destroy'])
            ->middleware('permission:order.update');
        Route::post('/{orderItem}/changes', [OrderItemChangeController::class, 'store'])
            ->middleware('permission:order.change');
        Route::post('/{orderItem}/replace', [OrderItemChangeController::class, 'replace'])
            ->middleware('permission:order.change');
    });

    // Order Batches
    Route::post('/order-batches/{orderBatch}/submit', [OrderBatchController::class, 'submit'])
        ->middleware('permission:order.create|order.update');
    Route::delete('/order-batches/{orderBatch}', [OrderBatchController::class, 'destroy'])
        ->middleware('permission:order.create|order.update');

    // Kitchen Display System (KDS)
    Route::prefix('kitchen')->middleware('permission:kitchen.view')->group(function () {
        Route::get('/orders', [KitchenController::class, 'index']);
        Route::patch('/batches/{batch}/accept', [KitchenActionController::class, 'acceptBatch'])
            ->middleware('permission:kitchen.update');
        Route::patch('/items/{item}/prepare', [KitchenActionController::class, 'prepareItem'])
            ->middleware('permission:kitchen.update');
        Route::patch('/items/{item}/ready', [KitchenActionController::class, 'readyItem'])
            ->middleware('permission:kitchen.update');
        Route::patch('/items/{item}/serve', [KitchenActionController::class, 'serveItem'])
            ->middleware('permission:kitchen.update');
    });

    // Reporting & Analytics Suite (Phase 1 & Phase 2)
    Route::get('/reports/dashboard', [DashboardController::class, 'summary'])
        ->middleware('permission:dashboard.view');

    Route::prefix('reports')->middleware('permission:report.view')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales']);
        Route::get('/payments', [ReportController::class, 'payments']);
        Route::get('/menu-sales', [ReportController::class, 'menuSales']);
        Route::get('/top-menu', [ReportController::class, 'menuSales']);
        Route::get('/inventory', [ReportController::class, 'inventory']);
        Route::get('/stock-movements', [ReportController::class, 'stockMovements']);
        Route::get('/profit/summary', [ProfitReportController::class, 'summary']);
        Route::get('/orders/{order}/profit', [ProfitReportController::class, 'orderProfit']);
    });

    // Audit Logs (Phase 4)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->middleware('permission:report.view|user.manage');
});
