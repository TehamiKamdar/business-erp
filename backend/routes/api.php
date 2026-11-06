<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'application' => 'Business ERP',
        'message' => 'API is running successfully',
    ]);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    
    // Authenticated routes
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    require __DIR__ . '/audit.php';
    require __DIR__ . '/product-catalog.php';
    require __DIR__ . '/suppliers.php';
    require __DIR__ . '/purchasing.php';
    require __DIR__ . '/inventory.php';

    // Test Routes
    Route::get('/test-permission', function () {
        return response()->json([
            'success' => true,
            'message' => 'Permission granted.',
        ]);
    })->middleware('permission:products.view');
});