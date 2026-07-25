<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are for external API access using API keys.
| All routes require a valid API key for authentication.
|
*/

// Public health check (no API key required)
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Protected API routes (require API key)
Route::middleware(\App\Http\Middleware\ValidateApiKey::class)->group(function () {
    
    // Store Information
    Route::get('/info', [ApiController::class, 'info']);
    
    // Products
    Route::get('/products', [ApiController::class, 'products']);
    Route::get('/products/{id}', [ApiController::class, 'product']);
    
    // Categories
    Route::get('/categories', [ApiController::class, 'categories']);
    
    // Orders (requires appropriate API key type)
    Route::get('/orders', [ApiController::class, 'orders']);
    Route::get('/orders/{id}', [ApiController::class, 'order']);
    
    // Customers (requires appropriate API key type)
    Route::get('/customers', [ApiController::class, 'customers']);
    
    // Staff Members (requires appropriate API key type)
    Route::get('/staffs', [ApiController::class, 'staffs']);
    Route::get('/staffs/{id}', [ApiController::class, 'staff']);
    
    // API Usage Stats
    Route::get('/usage', [ApiController::class, 'usage']);
    
});
