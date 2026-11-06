<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Inventory\StockAdjustmentController;

Route::prefix('stock-adjustments')->name('stock-adjustments.')->group(function () {

    Route::get('/', [StockAdjustmentController::class, 'index'])
        ->middleware('permission:stock_adjustments.view');

    Route::post('/', [StockAdjustmentController::class, 'store'])
        ->middleware('permission:stock_adjustments.create');

    Route::get('/{id}', [StockAdjustmentController::class, 'show'])
        ->middleware('permission:stock_adjustments.view');

    Route::put('/{id}', [StockAdjustmentController::class, 'update'])
        ->middleware('permission:stock_adjustments.edit');

    Route::patch('/{id}', [StockAdjustmentController::class, 'update'])
        ->middleware('permission:stock_adjustments.edit');

    Route::post('/{id}/approve', [StockAdjustmentController::class, 'approve'])
        ->middleware('permission:stock_adjustments.approve');

    Route::post('/{id}/post', [StockAdjustmentController::class, 'post'])
        ->middleware('permission:stock_adjustments.approve');

    Route::post('/{id}/cancel', [StockAdjustmentController::class, 'cancel'])
        ->middleware('permission:stock_adjustments.edit');

});