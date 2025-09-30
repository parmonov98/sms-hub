<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeliveryCallbackController extends Controller
{
    /**
     * Handle delivery status callbacks from SMS providers
     */
    public function handle(Request $request)
    {
        try {
            Log::info('SMS delivery callback received', [
                'request_data' => $request->all(),
                'headers' => $request->headers->all(),
            ]);

            // Handle Eskiz delivery callback
            if ($request->has('id') && $request->has('status')) {
                return $this->handleEskizCallback($request);
            }

            // Handle other providers if needed
            Log::warning('Unknown delivery callback format', [
                'request_data' => $request->all(),
            ]);

            return response()->json(['status' => 'unknown_format'], 400);

        } catch (\Exception $e) {
            Log::error('SMS delivery callback error', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Handle Eskiz delivery status callback
     */
    private function handleEskizCallback(Request $request)
    {
        $messageId = $request->input('id');
        $status = $request->input('status');
        $error = $request->input('error');

        Log::info('Eskiz delivery callback', [
            'message_id' => $messageId,
            'status' => $status,
            'error' => $error,
        ]);

        // Find the message by provider_message_id
        $message = Message::where('provider_message_id', $messageId)->first();

        if (!$message) {
            Log::warning('Message not found for delivery callback', [
                'provider_message_id' => $messageId,
            ]);
            return response()->json(['status' => 'message_not_found'], 404);
        }

        // Map Eskiz status to our status
        $mappedStatus = $this->mapEskizStatus($status);

        // Update message status
        $oldStatus = $message->status;
        $message->update([
            'status' => $mappedStatus,
            'error_message' => $error,
        ]);

        Log::info('Message status updated via callback', [
            'message_id' => $message->id,
            'provider_message_id' => $messageId,
            'old_status' => $oldStatus,
            'new_status' => $mappedStatus,
            'eskiz_status' => $status,
        ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Map Eskiz status to our internal status
     */
    private function mapEskizStatus(string $eskizStatus): string
    {
        return match (strtoupper($eskizStatus)) {
            'ACCEPTED' => 'sent',
            'DELIVERED' => 'delivered',
            'FAILED' => 'failed',
            'REJECTED' => 'failed',
            'EXPIRED' => 'failed',
            default => 'unknown',
        };
    }
}