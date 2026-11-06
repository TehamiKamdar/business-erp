<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Inventory\StockAdjustmentController;
use App\Http\Controllers\Api\Inventory\StockTransferController;

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

Route::prefix('stock-transfers')->name('stock-transfers.')->group(function () {

    Route::get('/', [StockTransferController::class, 'index'])
        ->middleware('permission:stock_transfers.view');

    Route::post('/', [StockTransferController::class, 'store'])
        ->middleware('permission:stock_transfers.create');

    Route::post('/{id}/dispatch', [StockTransferController::class, 'dispatch'])
        ->middleware('permission:stock_transfers.edit');

    Route::post('/{id}/receive', [StockTransferController::class, 'receive'])
        ->middleware('permission:stock_transfers.receive');

    Route::post('/{id}/cancel', [StockTransferController::class, 'cancel'])
        ->middleware('permission:stock_transfers.cancel');

    Route::get('/{id}', [StockTransferController::class, 'show'])
        ->middleware('permission:stock_transfers.view');

});