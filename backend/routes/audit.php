<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuditLogController;

Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
    Route::get('/', [AuditLogController::class, 'index'])
        ->middleware('permission:audit_logs.view');

    Route::get('/{id}', [AuditLogController::class, 'show'])
        ->middleware('permission:audit_logs.view');
});