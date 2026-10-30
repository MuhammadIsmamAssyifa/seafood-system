<?php

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\DashboardWebController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthWebController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthWebController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

    Route::middleware('permission:dashboard.view')->group(function () {
        Route::get('/', [DashboardWebController::class, 'index'])->name('dashboard.index');
        Route::get('/dashboard', [DashboardWebController::class, 'index'])->name('dashboard');
    });

    Route::get('/reports', [DashboardWebController::class, 'reports'])
        ->middleware('permission:report.view')
        ->name('dashboard.reports');

    Route::get('/inventory', [DashboardWebController::class, 'inventory'])
        ->middleware('permission:inventory.view')
        ->name('dashboard.inventory');

    Route::post('/inventory/stock', [DashboardWebController::class, 'storeStock'])
        ->middleware('permission:inventory.manage')
        ->name('dashboard.inventory.store');

    Route::get('/menus', [DashboardWebController::class, 'menus'])
        ->middleware('permission:menu.view')
        ->name('dashboard.menus');

    Route::middleware('permission:user.manage')->group(function () {
        Route::get('/users', [DashboardWebController::class, 'users'])->name('dashboard.users');
        Route::post('/users', [DashboardWebController::class, 'storeUser'])->name('dashboard.users.store');
    });

    Route::get('/audit-logs', [DashboardWebController::class, 'auditLogs'])
        ->middleware('permission:report.view|user.manage')
        ->name('dashboard.audit_logs');
});
