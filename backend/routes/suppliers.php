<?php

use App\Http\Controllers\Api\Suppliers\SupplierController;
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
});