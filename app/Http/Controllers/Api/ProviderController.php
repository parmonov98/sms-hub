<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ProviderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/providers",
     *     summary="List available SMS providers",
     *     description="Retrieve a list of all available SMS providers with their capabilities, status, and priority. This information helps you understand which providers are available for sending messages and their specific features like Unicode support, delivery reports, etc.",
     *     operationId="getProviders",
     *     tags={"Providers"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Providers retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Provider"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing access token",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index()
    {
        //
    }
}
