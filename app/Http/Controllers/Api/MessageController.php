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
     *     summary="Get list of SMS messages",
     *     description="Retrieve a paginated list of SMS messages for the authenticated user",
     *     operationId="getMessages",
     *     tags={"Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by message status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "sent", "delivered", "failed"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Message")),
     *             @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
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
     *     path="/api/v1/messages",
     *     summary="Send SMS message",
     *     description="Send a new SMS message through the SMS Hub",
     *     operationId="sendMessage",
     *     tags={"Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"to", "message"},
     *             @OA\Property(property="to", type="string", description="Recipient phone number", example="+998901234567"),
     *             @OA\Property(property="message", type="string", description="SMS message content", example="Hello, this is a test message!"),
     *             @OA\Property(property="provider", type="string", description="SMS provider to use", example="eskiz"),
     *             @OA\Property(property="sender_id", type="string", description="Sender ID", example="SMSHub"),
     *             @OA\Property(property="callback_url", type="string", description="Webhook URL for delivery reports", example="https://example.com/webhook"),
     *             @OA\Property(property="priority", type="integer", description="Message priority (1-5)", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Message"),
     *             @OA\Property(property="message", type="string", example="Message sent successfully")
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
     *         response=422,
     *         description="Unprocessable entity",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
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
     *     description="Retrieve detailed information about a specific SMS message",
     *     operationId="getMessage",
     *     tags={"Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Message ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show(string $id)
    {
        $message = \App\Models\Message::find($id);
        
        if (!$message) {
            return response()->json(['message' => 'Message not found'], 404);
        }
        
        return response()->json(['data' => $message]);
    }
}
