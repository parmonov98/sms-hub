<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    public function index(Request $request)
    {
        // Validate request parameters
        $validated = $request->validate([
            'period' => 'nullable|in:today,week,month,year',
            'provider' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Determine date range
        $period = $validated['period'] ?? 'month';
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        if (!$startDate || !$endDate) {
            [$startDate, $endDate] = $this->getDateRangeForPeriod($period);
        }

        // Build base query
        $baseQuery = Message::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // Filter by provider if specified
        if (isset($validated['provider'])) {
            $baseQuery->whereHas('provider', function ($q) use ($validated) {
                $q->where('display_name', $validated['provider']);
            });
        }

        // Get all messages statistics
        $allStats = (clone $baseQuery)->select(
            DB::raw('COUNT(*) as total_messages'),
            DB::raw('SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as successful_messages'),
            DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_messages')
        )->first();

        // Get cost only from delivered messages
        $costStats = (clone $baseQuery)
            ->where('status', 'delivered')
            ->select(
                DB::raw('SUM(COALESCE(price_decimal, 0)) as total_cost'),
                DB::raw('MAX(currency) as currency')
            )->first();

        $totalMessages = (int) ($allStats->total_messages ?? 0);
        $successfulMessages = (int) ($allStats->successful_messages ?? 0);
        $failedMessages = (int) ($allStats->failed_messages ?? 0);
        $totalCost = (float) ($costStats->total_cost ?? 0);
        $successRate = $totalMessages > 0 ? round(($successfulMessages / $totalMessages) * 100, 2) : 0;

        return response()->json([
            'data' => [
                'total_messages' => $totalMessages,
                'successful_messages' => $successfulMessages,
                'failed_messages' => $failedMessages,
                'total_cost' => $totalCost,
                'currency' => $costStats->currency ?? 'UZS',
                'success_rate' => $successRate,
                'period' => $period,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ]);
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
    public function daily(Request $request)
    {
        // Validate request parameters
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $validated['start_date'] ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $validated['end_date'] ?? now()->format('Y-m-d');

        // Query daily statistics for delivered messages
        $dailyStats = Message::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as messages_delivered'),
                DB::raw('SUM(COALESCE(price_decimal, 0)) as total_cost')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Get total messages per day for success rate calculation
        $dailyTotals = Message::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as messages_sent'),
                DB::raw('SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as messages_delivered'),
                DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as messages_failed')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->keyBy('date');

        $result = [];
        foreach ($dailyStats as $stat) {
            $date = $stat->date;
            $total = $dailyTotals->get($date);

            $messagesSent = $total ? (int) $total->messages_sent : 0;
            $messagesDelivered = (int) $stat->messages_delivered;
            $messagesFailed = $total ? (int) $total->messages_failed : 0;
            $totalCost = (float) $stat->total_cost;
            $successRate = $messagesSent > 0 ? round(($messagesDelivered / $messagesSent) * 100, 2) : 0;

            $result[] = [
                'date' => $date,
                'messages_sent' => $messagesSent,
                'messages_delivered' => $messagesDelivered,
                'messages_failed' => $messagesFailed,
                'total_cost' => $totalCost,
                'success_rate' => $successRate,
            ];
        }

        return response()->json(['data' => $result]);
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
    public function summary(Request $request)
    {
        // Current month
        [$currentStart, $currentEnd] = $this->getDateRangeForPeriod('month');
        $currentMonth = $this->calculateStats($currentStart, $currentEnd);

        // Last month
        $lastMonthStart = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $lastMonthEnd = now()->subMonth()->endOfMonth()->format('Y-m-d');
        $lastMonth = $this->calculateStats($lastMonthStart, $lastMonthEnd);

        // Lifetime (all time)
        $firstMessage = Message::orderBy('created_at')->first();
        $lifetimeStart = $firstMessage ? $firstMessage->created_at->format('Y-m-d') : now()->format('Y-m-d');
        $lifetimeEnd = now()->format('Y-m-d');
        $lifetime = $this->calculateStats($lifetimeStart, $lifetimeEnd);

        return response()->json([
            'data' => [
                'current_month' => $currentMonth,
                'last_month' => $lastMonth,
                'total_lifetime' => $lifetime,
            ]
        ]);
    }

    /**
     * Get date range for a given period.
     */
    private function getDateRangeForPeriod(string $period): array
    {
        return match ($period) {
            'today' => [now()->format('Y-m-d'), now()->format('Y-m-d')],
            'week' => [now()->subWeek()->format('Y-m-d'), now()->format('Y-m-d')],
            'month' => [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')],
            'year' => [now()->startOfYear()->format('Y-m-d'), now()->endOfYear()->format('Y-m-d')],
            default => [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')],
        };
    }

    /**
     * Calculate statistics for a date range.
     */
    private function calculateStats(string $startDate, string $endDate): array
    {
        // Get delivered messages stats
        $deliveredStats = Message::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                DB::raw('COUNT(*) as successful_messages'),
                DB::raw('SUM(COALESCE(price_decimal, 0)) as total_cost'),
                DB::raw('MAX(currency) as currency')
            )
            ->first();

        // Get all messages stats
        $allStats = Message::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                DB::raw('COUNT(*) as total_messages'),
                DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_messages')
            )
            ->first();

        $totalMessages = (int) ($allStats->total_messages ?? 0);
        $successfulMessages = (int) ($deliveredStats->successful_messages ?? 0);
        $failedMessages = (int) ($allStats->failed_messages ?? 0);
        $totalCost = (float) ($deliveredStats->total_cost ?? 0);
        $successRate = $totalMessages > 0 ? round(($successfulMessages / $totalMessages) * 100, 2) : 0;

        return [
            'total_messages' => $totalMessages,
            'successful_messages' => $successfulMessages,
            'failed_messages' => $failedMessages,
            'total_cost' => $totalCost,
            'currency' => $deliveredStats->currency ?? 'UZS',
            'success_rate' => $successRate,
            'period' => 'custom',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
