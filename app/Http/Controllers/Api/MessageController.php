<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class MessageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/messages",
     *     summary="List SMS messages",
     *     description="Retrieve a paginated list of SMS messages sent by your application. Use this endpoint to track message history, check delivery status, and monitor your SMS usage. You can filter by status and paginate through results.",
     *     operationId="getMessages",
     *     tags={"Messages"},
     *     security={{"bearer": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination (starts from 1)",
     *         required=false,
     *         @OA\Schema(type="integer", default=1, minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page (max 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=15, minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter messages by delivery status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"queued", "sent", "delivered", "failed"}, example="delivered")
     *     ),
     *     @OA\Parameter(
     *         name="from_date",
     *         in="query",
     *         description="Filter messages from this date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="to_date",
     *         in="query",
     *         description="Filter messages until this date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Messages retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Message")),
     *             @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing access token",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error - Invalid query parameters",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
    public function index()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/v1/messages",
     *     summary="Send SMS message",
     *     description="Send a new SMS message through the SMS Hub. The message will be queued for delivery and processed by the most suitable provider based on availability and priority. You can specify a preferred provider or let the system choose automatically.",
     *     operationId="sendMessage",
     *     tags={"Messages"},
     *     security={{"bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="SMS message details",
     *         @OA\JsonContent(
     *             required={"to", "message"},
     *             @OA\Property(
     *                 property="to",
     *                 type="string",
     *                 description="Recipient phone number in international format (E.164)",
     *                 example="+998901234567",
     *                 pattern="^\+[1-9]\d{1,14}$"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="SMS message content (max 1600 characters for Unicode, 160 for ASCII)",
     *                 example="Hello! Your verification code is 123456. Valid for 5 minutes.",
     *                 maxLength=1600
     *             ),
     *             @OA\Property(
     *                 property="provider",
     *                 type="string",
     *                 description="Preferred SMS provider (optional - system will choose if not specified)",
     *                 example="eskiz"
     *             ),
     *             @OA\Property(
     *                 property="sender_id",
     *                 type="string",
     *                 description="Sender ID (max 11 characters, alphanumeric only)",
     *                 example="SMSHub",
     *                 maxLength=11,
     *                 pattern="^[a-zA-Z0-9]+$"
     *             ),
     *             @OA\Property(
     *                 property="priority",
     *                 type="integer",
     *                 description="Message priority (1=highest, 5=lowest, default=3)",
     *                 example=1,
     *                 minimum=1,
     *                 maximum=5
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message queued for sending successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Message"),
     *             @OA\Property(property="message", type="string", example="Message queued for sending")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - Invalid phone number format or message content",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing access token",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error - Required fields missing or invalid format",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Rate limit exceeded - Too many requests",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Rate limit exceeded"),
     *             @OA\Property(property="message", type="string", example="Too many requests. Please try again later."),
     *             @OA\Property(property="retry_after", type="integer", example=60)
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'to' => 'required|string|max:20',
            'message' => 'required|string|max:1600',
            'provider' => 'nullable|string|exists:providers,display_name',
            'sender_id' => 'nullable|string|max:11',
            'callback_url' => 'nullable|url',
            'priority' => 'nullable|integer|min:1|max:5',
        ]);

        // Get the OAuth client ID from the request (set by our middleware)
        $clientId = $request->attributes->get('oauth_client_id');

        if (!$clientId) {
            return response()->json(['message' => 'Client authentication required'], 401);
        }

        // Create the message record (no project needed in new system)
        $message = \App\Models\Message::create([
            'to' => $validated['to'],
            'from' => $validated['sender_id'] ?? '4546', // Use approved Eskiz sender ID
            'text' => $validated['message'],
            'status' => 'queued',
            'idempotency_key' => uniqid('msg_', true),
        ]);

        // If a specific provider is requested, try to use it
        if (isset($validated['provider'])) {
            $provider = \App\Models\Provider::where('display_name', $validated['provider'])->first();
            if ($provider) {
                $message->update(['provider_id' => $provider->id]);
            }
        }

        // Dispatch the SMS job
        \App\Jobs\SendSmsJob::dispatch($message->id);

        return response()->json([
            'data' => $message->fresh(),
            'message' => 'Message queued for sending'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/messages/{id}",
     *     summary="Get message details",
     *     description="Retrieve detailed information about a specific SMS message including delivery status, cost, and provider information. Use this endpoint to check the current status of a message you've sent.",
     *     operationId="getMessage",
     *     tags={"Messages"},
     *     security={{"bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Unique message identifier",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing access token",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message not found - The specified message ID does not exist or you don't have access to it",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Not Found"),
     *             @OA\Property(property="message", type="string", example="Message not found"),
     *             @OA\Property(property="code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function show(int $id)
    {
        $message = \App\Models\Message::find($id);

        if (!$message) {
            return response()->json(['message' => 'Message not found'], 404);
        }

        return response()->json(['data' => $message]);
    }
}
