<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductPriceController;
use App\Http\Controllers\Api\ProductUnitController;
use App\Http\Controllers\Api\UnitController;
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

            // Units
            Route::prefix('units')
                ->name('units.')
                ->group(function () {

                    Route::get('/', [UnitController::class, 'index'])
                        ->middleware('permission:units.view');

                    Route::post('/', [UnitController::class, 'store'])
                        ->middleware('permission:units.create');

                    Route::get('/{id}', [UnitController::class, 'show'])
                        ->middleware('permission:units.view');

                    Route::put('/{id}', [UnitController::class, 'update'])
                        ->middleware('permission:units.edit');

                    Route::patch('/{id}', [UnitController::class, 'update'])
                        ->middleware('permission:units.edit');

                    Route::delete('/{id}', [UnitController::class, 'destroy'])
                        ->middleware('permission:units.archive');

                    Route::patch('/{id}/activate', [UnitController::class, 'activate'])
                        ->middleware('permission:units.activate');

                    Route::patch('/{id}/deactivate', [UnitController::class, 'deactivate'])
                        ->middleware('permission:units.deactivate');
                });

            // Brands
            Route::prefix('brands')
                ->name('brands.')
                ->group(function () {

                    Route::get('/', [BrandController::class, 'index'])
                        ->middleware('permission:brands.view');

                    Route::post('/', [BrandController::class, 'store'])
                        ->middleware('permission:brands.create');

                    Route::get('/{id}', [BrandController::class, 'show'])
                        ->middleware('permission:brands.view');

                    Route::put('/{id}', [BrandController::class, 'update'])
                        ->middleware('permission:brands.edit');

                    Route::patch('/{id}', [BrandController::class, 'update'])
                        ->middleware('permission:brands.edit');

                    Route::delete('/{id}', [BrandController::class, 'destroy'])
                        ->middleware('permission:brands.archive');

                    Route::patch('/{id}/activate', [BrandController::class, 'activate'])
                        ->middleware('permission:brands.activate');

                    Route::patch('/{id}/deactivate', [BrandController::class, 'deactivate'])
                        ->middleware('permission:brands.deactivate');
                });

            // Products
            Route::prefix('products')->group(function () {
                Route::get('/', [ProductController::class, 'index'])
                    ->middleware('permission:products.view');

                Route::post('/', [ProductController::class, 'store'])
                    ->middleware('permission:products.create');

                Route::get('/{id}', [ProductController::class, 'show'])
                    ->middleware('permission:products.view');

                Route::put('/{id}', [ProductController::class, 'update'])
                    ->middleware('permission:products.edit');

                Route::patch('/{id}', [ProductController::class, 'update'])
                    ->middleware('permission:products.edit');

                Route::delete('/{id}', [ProductController::class, 'destroy'])
                    ->middleware('permission:products.archive');

                Route::patch('/{id}/activate', [ProductController::class, 'activate'])
                    ->middleware('permission:products.activate');

                Route::patch('/{id}/deactivate', [ProductController::class, 'deactivate'])
                    ->middleware('permission:products.deactivate');
            });

            // Product Units
            Route::prefix('products/{productId}/units')->group(function () {
                Route::get('/', [ProductUnitController::class, 'index'])
                    ->middleware('permission:products.view');

                Route::post('/', [ProductUnitController::class, 'store'])
                    ->middleware('permission:products.edit');

                Route::get('/{id}', [ProductUnitController::class, 'show'])
                    ->middleware('permission:products.view');

                Route::put('/{id}', [ProductUnitController::class, 'update'])
                    ->middleware('permission:products.edit');

                Route::patch('/{id}', [ProductUnitController::class, 'update'])
                    ->middleware('permission:products.edit');

                Route::delete('/{id}', [ProductUnitController::class, 'destroy'])
                    ->middleware('permission:products.archive');

                Route::patch('/{id}/activate', [ProductUnitController::class, 'activate'])
                    ->middleware('permission:products.activate');

                Route::patch('/{id}/deactivate', [ProductUnitController::class, 'deactivate'])
                    ->middleware('permission:products.deactivate');
            });

            // Product Prices
            Route::prefix('products/{productId}/units/{productUnitId}/prices')->group(function () {

                Route::get('/', [ProductPriceController::class, 'index'])
                    ->middleware('permission:products.view');

                Route::post('/', [ProductPriceController::class, 'store'])
                    ->middleware('permission:products.edit');

                Route::get('/{id}', [ProductPriceController::class, 'show'])
                    ->middleware('permission:products.view');

                Route::put('/{id}', [ProductPriceController::class, 'update'])
                    ->middleware('permission:products.edit');

                Route::patch('/{id}', [ProductPriceController::class, 'update'])
                    ->middleware('permission:products.edit');

                Route::delete('/{id}', [ProductPriceController::class, 'destroy'])
                    ->middleware('permission:products.archive');

                Route::patch('/{id}/activate', [ProductPriceController::class, 'activate'])
                    ->middleware('permission:products.activate');

                Route::patch('/{id}/deactivate', [ProductPriceController::class, 'deactivate'])
                    ->middleware('permission:products.deactivate');
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