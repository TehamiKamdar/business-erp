<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'application' => 'Business ERP',
        'message' => 'API is running successfully',
    ]);
});