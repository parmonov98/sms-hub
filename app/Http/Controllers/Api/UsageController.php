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
     *     description="Retrieve comprehensive SMS usage statistics for your account. This endpoint provides detailed insights into your SMS consumption, costs, and delivery performance across different time periods and providers.",
     *     operationId="getUsage",
     *     tags={"Usage"},
     *     security={{"bearer": {}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Time period for statistics aggregation",
     *         required=false,
     *         @OA\Schema(type="string", enum={"today", "week", "month", "year"}, default="month", example="month")
     *     ),
     *     @OA\Parameter(
     *         name="provider",
     *         in="query",
     *         description="Filter statistics by specific provider",
     *         required=false,
     *         @OA\Schema(type="string", example="eskiz")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Custom start date for statistics (YYYY-MM-DD format)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Custom end date for statistics (YYYY-MM-DD format)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usage statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/UsageStats")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing access token",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error - Invalid date format or parameters",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
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
     *     security={{"bearer": {}}},
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
     *     security={{"bearer": {}}},
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
