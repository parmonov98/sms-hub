<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
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
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="invalid_request"),
     *             @OA\Property(property="error_description", type="string", example="The request is missing a required parameter"),
     *             @OA\Property(property="hint", type="string", example="Check the grant_type parameter")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - invalid client credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="invalid_client"),
     *             @OA\Property(property="error_description", type="string", example="Client authentication failed")
     *         )
     *     )
     * )
     */
    public function getToken(Request $request)
    {
        return app()->make('oauth2-server.builder')
            ->getAccessTokenResponse();
    }

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
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="invalid_grant"),
     *             @OA\Property(property="error_description", type="string", example="The refresh token is invalid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - invalid refresh token",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="invalid_client"),
     *             @OA\Property(property="error_description", type="string", example="Client authentication failed")
     *         )
     *     )
     * )
     */
    public function refreshToken(Request $request)
    {
        return app()->make('oauth2-server.builder')
            ->getAccessTokenResponse();
    }

    /**
     * @OA\Get(
     *     path="/v1/auth/me",
     *     summary="Get current user info",
     *     description="Get information about the currently authenticated user",
     *     operationId="getCurrentUser",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User information retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function me(Request $request)
    {
        return response()->json([
            'data' => $request->user()
        ]);
    }
}
