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
        //
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
        //
    }

    /**
     * @OA\Put(
     *     path="/api/v1/messages/{id}",
     *     summary="Update message",
     *     description="Update an existing SMS message (only if not sent)",
     *     operationId="updateMessage",
     *     tags={"Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Message ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Updated SMS message content"),
     *             @OA\Property(property="priority", type="integer", description="Updated message priority (1-5)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Message"),
     *             @OA\Property(property="message", type="string", example="Message updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - message already sent",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/messages/{id}",
     *     summary="Delete message",
     *     description="Delete an SMS message (only if not sent)",
     *     operationId="deleteMessage",
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
     *         description="Message deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Message deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - message already sent",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
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
    public function destroy(string $id)
    {
        //
    }
}
