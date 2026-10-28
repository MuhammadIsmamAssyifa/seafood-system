<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\OrderBatchController;
use App\Http\Controllers\Api\KitchenController;
use App\Http\Controllers\Api\KitchenActionController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\OrderItemChangeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/staff-login', [AuthController::class, 'staffLogin']);
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('catalog')->group(function () {
        Route::get('/menus', [CatalogController::class, 'menus']);
        Route::get('/stations', [CatalogController::class, 'stations']);
    });

    Route::prefix('orders')->group(function () {
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{order}', [OrderController::class, 'show']);
        Route::post('/{order}/items', [OrderItemController::class, 'store']);
        Route::post('/{order}/payments', [PaymentController::class, 'store']);
        Route::get('/{order}/payments/summary', [PaymentController::class, 'summary']);
    });

    Route::prefix('order-item-changes')->group(function () {
        Route::patch('/{change}/approve', [OrderItemChangeController::class, 'approve']);
        Route::patch('/{change}/reject', [OrderItemChangeController::class, 'reject']);
    });

    Route::prefix('order-items')->group(function () {
        Route::patch('/{orderItem}', [OrderItemController::class, 'update']);
        Route::delete('/{orderItem}', [OrderItemController::class, 'destroy']);
        Route::post('/{orderItem}/changes', [OrderItemChangeController::class, 'store']);
        Route::post('/{orderItem}/replace', [OrderItemChangeController::class, 'replace']);
    });

    Route::post('/order-batches/{orderBatch}/submit', [OrderBatchController::class, 'submit']);

    Route::prefix('kitchen')->group(function () {
        Route::get('/orders', [KitchenController::class, 'index']);
        Route::patch('/batches/{batch}/accept', [KitchenActionController::class, 'acceptBatch']);
        Route::patch('/items/{item}/prepare', [KitchenActionController::class, 'prepareItem']);
        Route::patch('/items/{item}/ready', [KitchenActionController::class, 'readyItem']);
        Route::patch('/items/{item}/serve', [KitchenActionController::class, 'serveItem']);
    });
});
