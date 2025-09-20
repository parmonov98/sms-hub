<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class UsageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/usage",
     *     summary="Get usage statistics",
     *     description="Retrieve SMS usage statistics for the authenticated user",
     *     operationId="getUsage",
     *     tags={"Usage"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Time period for statistics",
     *         required=false,
     *         @OA\Schema(type="string", enum={"today", "week", "month", "year"}, default="month")
     *     ),
     *     @OA\Parameter(
     *         name="provider",
     *         in="query",
     *         description="Filter by provider",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/UsageStats")
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
     * @OA\Get(
     *     path="/api/v1/usage/daily",
     *     summary="Get daily usage statistics",
     *     description="Retrieve daily SMS usage statistics",
     *     operationId="getDailyUsage",
     *     tags={"Usage"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DailyUsage"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function daily()
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/v1/usage/summary",
     *     summary="Get usage summary",
     *     description="Retrieve a summary of SMS usage statistics",
     *     operationId="getUsageSummary",
     *     tags={"Usage"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/UsageSummary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function summary()
    {
        //
    }
}
