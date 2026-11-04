<?php

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