<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
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

    // Product Catalog Routes
    Route::prefix('product-catalog')
        ->name('product-catalog.')
        ->group(function () {

            // Categories
            Route::prefix('categories')
                ->name('categories.')
                ->group(function () {

                    Route::get('/', [CategoryController::class, 'index'])
                        ->middleware('permission:categories.view');

                    Route::post('/', [CategoryController::class, 'store'])
                        ->middleware('permission:categories.create');

                    Route::get('/{id}', [CategoryController::class, 'show'])
                        ->middleware('permission:categories.view');

                    Route::put('/{id}', [CategoryController::class, 'update'])
                        ->middleware('permission:categories.edit');

                    Route::patch('/{id}', [CategoryController::class, 'update'])
                        ->middleware('permission:categories.edit');

                    Route::delete('/{id}', [CategoryController::class, 'destroy'])
                        ->middleware('permission:categories.archive');

                    Route::patch('/{id}/activate', [CategoryController::class, 'activate'])
                        ->middleware('permission:categories.activate');

                    Route::patch('/{id}/deactivate', [CategoryController::class, 'deactivate'])
                        ->middleware('permission:categories.deactivate');
                });

            // Products
            Route::middleware('permission:products.view')->group(function () {
                // Product routes will be added here
            });

            // Units
            Route::middleware('permission:units.view')->group(function () {
                // Unit routes will be added here
            });

            // Brands
            Route::middleware('permission:brands.view')->group(function () {
                // Brand routes will be added here
            });
        });

    // Test Routes
    Route::get('/test-permission', function () {
        return response()->json([
            'success' => true,
            'message' => 'Permission granted.',
        ]);
    })->middleware('permission:products.view');
});