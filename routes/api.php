<?php

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

/**
 * @OA\Post(
 *     path="/oauth/token",
 *     summary="Get OAuth2 access token",
 *     description="Obtain an access token using OAuth2 client credentials or password grant",
 *     operationId="getToken",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"grant_type", "client_id", "client_secret"},
 *             @OA\Property(property="grant_type", type="string", enum={"client_credentials", "password"}, description="OAuth2 grant type", example="client_credentials"),
 *             @OA\Property(property="client_id", type="string", description="OAuth2 client ID", example="01996688-68b6-73da-b265-98d48d707a69"),
 *             @OA\Property(property="client_secret", type="string", description="OAuth2 client secret", example="your-client-secret"),
 *             @OA\Property(property="username", type="string", description="Username (for password grant)", example="user@example.com"),
 *             @OA\Property(property="password", type="string", description="Password (for password grant)", example="password"),
 *             @OA\Property(property="scope", type="string", description="Requested scopes", example="read write")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Token obtained successfully",
 *         @OA\JsonContent(ref="#/components/schemas/TokenResponse")
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request - invalid grant type or credentials",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - invalid client credentials",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
Route::post('/oauth/token', function () {
    return app()->make('oauth2-server.builder')
        ->getAccessTokenResponse();
});

/**
 * @OA\Post(
 *     path="/v1/auth/refresh",
 *     summary="Refresh OAuth2 access token",
 *     description="Refresh an expired access token using refresh token",
 *     operationId="refreshToken",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"grant_type", "refresh_token"},
 *             @OA\Property(property="grant_type", type="string", description="Grant type", example="refresh_token"),
 *             @OA\Property(property="refresh_token", type="string", description="Refresh token", example="def50200..."),
 *             @OA\Property(property="client_id", type="string", description="OAuth2 client ID", example="01996688-68b6-73da-b265-98d48d707a69"),
 *             @OA\Property(property="client_secret", type="string", description="OAuth2 client secret", example="your-client-secret")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Token refreshed successfully",
 *         @OA\JsonContent(ref="#/components/schemas/TokenResponse")
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request - invalid refresh token",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - invalid refresh token",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
Route::post('/v1/auth/refresh', function () {
    return app()->make('oauth2-server.builder')
        ->getAccessTokenResponse();
});

// Protected API routes
Route::middleware('auth:api')->group(function () {
    // API v1 routes
    Route::prefix('v1')->group(function () {
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
