<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Sales\SalesOrderController;

Route::prefix('sales-orders')->group(function () {

    Route::get('/', [SalesOrderController::class, 'index'])
        ->middleware('permission:sales_orders.view');

    Route::post('/', [SalesOrderController::class, 'store'])
        ->middleware('permission:sales_orders.create');

    Route::get('/{salesOrderId}', [SalesOrderController::class, 'show'])
        ->middleware('permission:sales_orders.view');

    Route::post('/{salesOrderId}/approve', [SalesOrderController::class, 'approve'])
        ->middleware('permission:sales_orders.approve');

    Route::post('/{salesOrderId}/reject', [SalesOrderController::class, 'reject'])
        ->middleware('permission:sales_orders.reject');
});