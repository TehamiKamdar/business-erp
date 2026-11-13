<?php

use App\Http\Controllers\Api\Accounting\ChartOfAccountController;
use Illuminate\Support\Facades\Route;

Route::prefix('chart-of-accounts')->group(function () {

    Route::get('/', [ChartOfAccountController::class, 'index'])
        ->middleware('permission:chart_of_accounts.view');

    Route::post('/', [ChartOfAccountController::class, 'store'])
        ->middleware('permission:chart_of_accounts.create');

    Route::get('/{accountId}', [ChartOfAccountController::class, 'show'])
        ->middleware('permission:chart_of_accounts.view');

    Route::put('/{accountId}', [ChartOfAccountController::class, 'update'])
        ->middleware('permission:chart_of_accounts.edit');

    Route::post('/{accountId}/deactivate', [ChartOfAccountController::class, 'deactivate'])
        ->middleware('permission:chart_of_accounts.deactivate');

});