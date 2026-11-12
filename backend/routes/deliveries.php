<?php

use App\Http\Controllers\Api\Sales\DeliveryController;
use Illuminate\Support\Facades\Route;

Route::prefix('deliveries')->group(function () {

    Route::get('/', [DeliveryController::class, 'index'])
        ->middleware('permission:deliveries.view');

    Route::post('/', [DeliveryController::class, 'store'])
        ->middleware('permission:deliveries.create');

    Route::get('/{id}', [DeliveryController::class, 'show'])
        ->middleware('permission:deliveries.view');

    Route::put('/{id}', [DeliveryController::class, 'update'])
        ->middleware('permission:deliveries.edit');

    Route::post('/{id}/dispatch', [DeliveryController::class, 'dispatch'])
        ->middleware('permission:deliveries.approve');

    Route::post('/{id}/deliver', [DeliveryController::class, 'deliver'])
        ->middleware('permission:deliveries.edit');

    Route::post('/{id}/cancel', [DeliveryController::class, 'cancel'])
        ->middleware('permission:deliveries.cancel');
});