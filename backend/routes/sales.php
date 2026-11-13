<?php

use App\Http\Controllers\Api\Sales\CustomerPriceController;
use App\Http\Controllers\Api\Sales\SalesInvoiceController;
use App\Http\Controllers\Api\Sales\SalesOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer-prices')->group(function () {

    Route::get('/', [CustomerPriceController::class, 'index'])
        ->middleware('permission:customer_prices.view');

    Route::post('/', [CustomerPriceController::class, 'store'])
        ->middleware('permission:customer_prices.create');

    Route::get('/{priceId}', [CustomerPriceController::class, 'show'])
        ->middleware('permission:customer_prices.view');

    Route::put('/{priceId}', [CustomerPriceController::class, 'update'])
        ->middleware('permission:customer_prices.edit');

    Route::delete('/{priceId}', [CustomerPriceController::class, 'destroy'])
        ->middleware('permission:customer_prices.delete');

});

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

    Route::post('/{salesOrderId}/cancel', [SalesOrderController::class, 'cancel'])
        ->middleware('permission:sales_orders.cancel');
});

Route::prefix('sales-invoices')->group(function () {
    Route::get('/', [SalesInvoiceController::class, 'index'])
        ->middleware('permission:sales_invoices.view');

    Route::post('/', [SalesInvoiceController::class, 'store'])
        ->middleware('permission:sales_invoices.create');

    Route::get('/{id}', [SalesInvoiceController::class, 'show'])
        ->middleware('permission:sales_invoices.view');

        // This route to test later
    Route::put('/{id}', [SalesInvoiceController::class, 'update'])
        ->middleware('permission:sales_invoices.edit');

    Route::post('/{id}/approve', [SalesInvoiceController::class, 'approve'])
        ->middleware('permission:sales_invoices.approve');

    Route::post('/{id}/cancel', [SalesInvoiceController::class, 'cancel'])
        ->middleware('permission:sales_invoices.cancel');
});