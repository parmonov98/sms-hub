<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="SMS Hub API",
 *     version="1.0.0",
 *     description="SMS Hub API Documentation - A comprehensive SMS gateway service with multiple provider support",
 *     @OA\Contact(
 *         email="support@smshub.com",
 *         name="SMS Hub Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Development Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your JWT token"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="passport",
 *     type="oauth2",
 *     description="Laravel Passport OAuth2",
 *     @OA\Flow(
 *         flow="clientCredentials",
 *         tokenUrl="/oauth/token",
 *         scopes={
 *             "read": "Read access",
 *             "write": "Write access",
 *             "admin": "Admin access"
 *         }
 *     ),
 *     @OA\Flow(
 *         flow="password",
 *         tokenUrl="/oauth/token",
 *         scopes={
 *             "read": "Read access",
 *             "write": "Write access",
 *             "admin": "Admin access"
 *         }
 *     )
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="OAuth2 authentication endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Providers",
 *     description="SMS provider management"
 * )
 * 
 * @OA\Tag(
 *     name="Messages",
 *     description="SMS message operations"
 * )
 * 
 * @OA\Tag(
 *     name="Usage",
 *     description="Usage statistics and monitoring"
 * )
 * 
 * @OA\Tag(
 *     name="Admin",
 *     description="Administrative operations"
 * )
 * 
 * @OA\Tag(
 *     name="Webhooks",
 *     description="Webhook endpoints for delivery reports"
 * )
 */
abstract class Controller
{
    //
}