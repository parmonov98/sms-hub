<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="OAuth2 authentication endpoints for obtaining access tokens"
 * )
 *
 * @OA\Tag(
 *     name="Messages",
 *     description="SMS message operations - send messages and check delivery status"
 * )
 *
 * @OA\Tag(
 *     name="Providers",
 *     description="SMS provider information and management"
 * )
 *
 * @OA\Tag(
 *     name="Usage",
 *     description="Usage statistics and billing information"
 * )
 *
 * @OA\Tag(
 *     name="Admin",
 *     description="Administrative operations for managing providers and projects"
 * )
 */
abstract class Controller
{
    //
}
