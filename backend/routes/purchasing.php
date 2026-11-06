<?php

use App\Http\Controllers\Api\Purchasing\GoodsReceiptController;
use App\Http\Controllers\Api\Purchasing\PurchaseOrderController;
use App\Http\Controllers\Api\Purchasing\PurchaseRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('purchase-requests')->name('purchase-requests.')->group(function () {
    Route::get('/', [PurchaseRequestController::class, 'index'])
        ->middleware('permission:purchase_requests.view');

    Route::post('/', [PurchaseRequestController::class, 'store'])
        ->middleware('permission:purchase_requests.create');

    Route::get('/{id}', [PurchaseRequestController::class, 'show'])
        ->middleware('permission:purchase_requests.view');

    Route::put('/{id}', [PurchaseRequestController::class, 'update'])
        ->middleware('permission:purchase_requests.edit');

    Route::patch('/{id}', [PurchaseRequestController::class, 'update'])
        ->middleware('permission:purchase_requests.edit');

    Route::post('/{id}/submit', [PurchaseRequestController::class, 'submit'])
        ->middleware('permission:purchase_requests.submit');

    Route::post('/{id}/approve', [PurchaseRequestController::class, 'approve'])
        ->middleware('permission:purchase_requests.approve');

    Route::post('/{id}/reject', [PurchaseRequestController::class, 'reject'])
        ->middleware('permission:purchase_requests.reject');
});

Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {

    Route::get('/', [PurchaseOrderController::class, 'index'])
        ->middleware('permission:purchase_orders.view');

    Route::post('/', [PurchaseOrderController::class, 'store'])
        ->middleware('permission:purchase_orders.create');

    Route::get('/{id}', [PurchaseOrderController::class, 'show'])
        ->middleware('permission:purchase_orders.view');

    Route::put('/{id}', [PurchaseOrderController::class, 'update'])
        ->middleware('permission:purchase_orders.edit');

    Route::post('/{id}/submit', [PurchaseOrderController::class, 'submit'])
        ->middleware('permission:purchase_orders.submit');

    Route::post('/{id}/approve', [PurchaseOrderController::class, 'approve'])
        ->middleware('permission:purchase_orders.approve');

    Route::post('/{id}/cancel', [PurchaseOrderController::class, 'cancel'])
        ->middleware('permission:purchase_orders.cancel');

});

Route::prefix('goods-receipts')->name('goods-receipts.')->group(function () {

    Route::get('/', [GoodsReceiptController::class, 'index'])
        ->middleware('permission:goods_receipts.view');

    Route::post('/', [GoodsReceiptController::class, 'store'])
        ->middleware('permission:goods_receipts.create');

    Route::get('/{id}', [GoodsReceiptController::class, 'show'])
        ->middleware('permission:goods_receipts.view');

    Route::put('/{id}', [GoodsReceiptController::class, 'update'])
        ->middleware('permission:goods_receipts.edit');

    Route::patch('/{id}', [GoodsReceiptController::class, 'update'])
        ->middleware('permission:goods_receipts.edit');

});