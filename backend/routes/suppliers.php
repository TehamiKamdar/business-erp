<?php

use App\Http\Controllers\Api\Suppliers\SupplierController;
use App\Http\Controllers\Api\Suppliers\SupplierAddressController;
use Illuminate\Support\Facades\Route;

Route::prefix('suppliers')->group(function () {
    Route::get('/', [SupplierController::class, 'index'])
        ->middleware('permission:suppliers.view');

    Route::post('/', [SupplierController::class, 'store'])
        ->middleware('permission:suppliers.create');

    Route::get('/{id}', [SupplierController::class, 'show'])
        ->middleware('permission:suppliers.view');

    Route::put('/{id}', [SupplierController::class, 'update'])
        ->middleware('permission:suppliers.edit');

    Route::patch('/{id}', [SupplierController::class, 'update'])
        ->middleware('permission:suppliers.edit');

    Route::delete('/{id}', [SupplierController::class, 'destroy'])
        ->middleware('permission:suppliers.archive');

    Route::patch('/{id}/activate', [SupplierController::class, 'activate'])
        ->middleware('permission:suppliers.activate');

    Route::patch('/{id}/deactivate', [SupplierController::class, 'deactivate'])
        ->middleware('permission:suppliers.deactivate');

        // Supplier Addresses
        Route::prefix('{supplierId}/addresses')->group(function () {

            Route::get('/', [SupplierAddressController::class, 'index'])
                ->middleware('permission:suppliers.view');

            Route::post('/', [SupplierAddressController::class, 'store'])
                ->middleware('permission:suppliers.edit');

            Route::get('/{id}', [SupplierAddressController::class, 'show'])
                ->middleware('permission:suppliers.view');

            Route::put('/{id}', [SupplierAddressController::class, 'update'])
                ->middleware('permission:suppliers.edit');

            Route::patch('/{id}', [SupplierAddressController::class, 'update'])
                ->middleware('permission:suppliers.edit');

            Route::delete('/{id}', [SupplierAddressController::class, 'destroy'])
                ->middleware('permission:suppliers.edit');
        });
});