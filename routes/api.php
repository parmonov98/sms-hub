<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\UsageController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Admin\ProviderController as AdminProviderController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// OAuth2 Authentication routes
Route::post('/oauth/token', [AuthController::class, 'getToken']);
Route::post('/v1/auth/refresh', [AuthController::class, 'refreshToken']);

// Protected API routes
Route::middleware('auth:api')->group(function () {
    // API v1 routes
    Route::prefix('v1')->group(function () {
        // Authentication
        Route::get('/auth/me', [AuthController::class, 'me']);
        
        // Providers
        Route::get('/providers', [ProviderController::class, 'index']);
        
        // Messages
        Route::post('/messages', [MessageController::class, 'store']);
        Route::get('/messages/{message}', [MessageController::class, 'show']);
        
        // Usage
        Route::get('/usage', [UsageController::class, 'index']);
    });
});

// Admin routes
Route::middleware(['auth:api', 'admin'])->prefix('v1/admin')->group(function () {
    // Admin Providers
    Route::apiResource('providers', AdminProviderController::class);
    
    // Admin Projects
    Route::apiResource('projects', AdminProjectController::class);
    
    // Project Provider Credentials
    Route::post('/projects/{project}/providers', [AdminProjectController::class, 'attachProvider']);
    Route::patch('/projects/{project}/providers/{provider}', [AdminProjectController::class, 'updateProvider']);
    Route::delete('/projects/{project}/providers/{provider}', [AdminProjectController::class, 'detachProvider']);
});

// Webhook routes (no authentication required)
Route::prefix('v1/webhooks')->group(function () {
    Route::post('/{provider}/dlr', [WebhookController::class, 'deliveryReport']);
});
