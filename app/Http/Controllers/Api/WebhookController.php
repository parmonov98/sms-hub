<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class WebhookController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/webhooks/{provider}/dlr",
     *     summary="Delivery report webhook",
     *     description="Receive delivery reports from SMS providers",
     *     operationId="deliveryReport",
     *     tags={"Webhooks"},
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         description="SMS provider name",
     *         required=true,
     *         @OA\Schema(type="string", enum={"eskiz", "playmobile"})
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message_id", "status"},
     *             @OA\Property(property="message_id", type="string", description="Message ID", example="12345"),
     *             @OA\Property(property="status", type="string", description="Delivery status", example="delivered"),
     *             @OA\Property(property="error_code", type="string", description="Error code if failed", example=""),
     *             @OA\Property(property="error_message", type="string", description="Error message if failed", example=""),
     *             @OA\Property(property="delivered_at", type="string", format="datetime", description="Delivery timestamp", example="2024-01-15T10:30:00Z"),
     *             @OA\Property(property="cost", type="number", format="float", description="Message cost", example=0.05)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webhook processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Delivery report processed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - invalid webhook data",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function deliveryReport(Request $request, string $provider)
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/v1/webhooks/{provider}/status",
     *     summary="Status update webhook",
     *     description="Receive status updates from SMS providers",
     *     operationId="statusUpdate",
     *     tags={"Webhooks"},
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         description="SMS provider name",
     *         required=true,
     *         @OA\Schema(type="string", enum={"eskiz", "playmobile"})
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message_id", "status"},
     *             @OA\Property(property="message_id", type="string", description="Message ID", example="12345"),
     *             @OA\Property(property="status", type="string", description="Message status", example="sent"),
     *             @OA\Property(property="provider_message_id", type="string", description="Provider's message ID", example="provider_12345"),
     *             @OA\Property(property="updated_at", type="string", format="datetime", description="Status update timestamp", example="2024-01-15T10:30:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status update processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Status update processed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - invalid webhook data",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function statusUpdate(Request $request, string $provider)
    {
        //
    }
}
