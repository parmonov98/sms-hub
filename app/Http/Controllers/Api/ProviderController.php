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
     *     summary="Get list of SMS providers",
     *     description="Retrieve a list of available SMS providers",
     *     operationId="getProviders",
     *     tags={"Providers"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Provider"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/v1/providers",
     *     summary="Create new SMS provider",
     *     description="Create a new SMS provider configuration",
     *     operationId="createProvider",
     *     tags={"Providers"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "type", "credentials"},
     *             @OA\Property(property="name", type="string", description="Provider name", example="Eskiz"),
     *             @OA\Property(property="type", type="string", description="Provider type", example="eskiz"),
     *             @OA\Property(property="description", type="string", description="Provider description", example="Eskiz SMS provider for Uzbekistan"),
     *             @OA\Property(property="credentials", type="object", description="Provider credentials", example={"api_key": "your_api_key", "sender_id": "SMSHub"}),
     *             @OA\Property(property="is_active", type="boolean", description="Provider status", example=true),
     *             @OA\Property(property="priority", type="integer", description="Provider priority", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Provider created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Provider"),
     *             @OA\Property(property="message", type="string", example="Provider created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/v1/providers/{id}",
     *     summary="Get provider details",
     *     description="Retrieve detailed information about a specific SMS provider",
     *     operationId="getProvider",
     *     tags={"Providers"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Provider ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Provider")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Provider not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(string $id)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/v1/providers/{id}",
     *     summary="Update provider",
     *     description="Update an existing SMS provider configuration",
     *     operationId="updateProvider",
     *     tags={"Providers"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Provider ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Provider name"),
     *             @OA\Property(property="description", type="string", description="Provider description"),
     *             @OA\Property(property="credentials", type="object", description="Provider credentials"),
     *             @OA\Property(property="is_active", type="boolean", description="Provider status"),
     *             @OA\Property(property="priority", type="integer", description="Provider priority")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Provider updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Provider"),
     *             @OA\Property(property="message", type="string", example="Provider updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Provider not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/providers/{id}",
     *     summary="Delete provider",
     *     description="Delete an SMS provider configuration",
     *     operationId="deleteProvider",
     *     tags={"Providers"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Provider ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Provider deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Provider deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Provider not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy(string $id)
    {
        //
    }
}
