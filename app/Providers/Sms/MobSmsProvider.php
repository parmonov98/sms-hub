<?php

namespace App\Providers\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MobSMS Cloud provider (https://api.mobsms.cloud).
 *
 * MobSMS is an Android-gateway SMS service: messages are sent from the
 * account's active gateway device. It authenticates with a static API key
 * (no OAuth token exchange) and has no sender-ID or template-moderation
 * requirements, so the $from argument is ignored.
 */
class MobSmsProvider implements SmsProviderInterface
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://api.mobsms.cloud', '/');
    }

    public function send(string $to, string $from, string $text, array $options = []): array
    {
        try {
            if (!$this->apiKey) {
                return [
                    'status' => 'failed',
                    'error' => 'No MobSMS API key configured',
                ];
            }

            $payload = [
                'recipient_number' => $to,
                'message' => $text,
                'callback_url' => $options['callback_url'] ?? url('/api/v1/sms/delivery-callback'),
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->asJson()->post($this->baseUrl . '/api/sms', $payload);

            $data = $response->json();

            if ($response->successful() && ($data['success'] ?? false)) {
                $message = $data['data'] ?? [];

                return [
                    // A successful accept means "queued for sending" at the
                    // gateway. SmsService treats only 'sent' as success, so
                    // report 'sent' here (like EskizProvider); the real
                    // delivered/failed outcome is resolved later by checkStatus.
                    'status' => 'sent',
                    // Store the numeric id: GET /api/sms/{id} (used by checkStatus)
                    // accepts the numeric id or the message_id UUID.
                    'message_id' => $message['id'] ?? ($message['message_id'] ?? null),
                    'cost' => $message['price'] ?? 0,
                    'currency' => 'UZS',
                    'provider_response' => $data,
                ];
            }

            $errorMessage = $data['message'] ?? 'Unknown error';

            Log::error('MobSMS send failed', [
                'response' => $data,
                'status' => $response->status(),
                'error_message' => $errorMessage,
                'to' => $to,
            ]);

            return [
                'status' => 'failed',
                'error' => $errorMessage,
                'provider_response' => $data,
                'status_code' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('MobSMS send exception', [
                'error' => $e->getMessage(),
                'to' => $to,
            ]);

            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function checkStatus(string $messageId): array
    {
        try {
            if (!$this->apiKey) {
                return [
                    'status' => 'unknown',
                    'error' => 'No MobSMS API key configured',
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/api/sms/' . $messageId);

            $data = $response->json();

            if ($response->successful() && ($data['success'] ?? false)) {
                $message = $data['data'] ?? [];

                return [
                    'status' => $this->mapStatus($message['status'] ?? 'unknown'),
                    'cost' => $message['price'] ?? null,
                    'provider_response' => $data,
                ];
            }

            return [
                'status' => 'unknown',
                'error' => $data['message'] ?? 'Failed to check status',
                'provider_response' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('MobSMS status check failed', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'unknown',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getCapabilities(): array
    {
        return [
            'dlr' => true,      // Delivery reports (via callback / status poll)
            'unicode' => true,  // Unicode support
            'concat' => true,   // Concatenated messages
            'flash' => false,   // Flash messages
        ];
    }

    public function getName(): string
    {
        return 'mobsms';
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['api_key']);
    }

    /**
     * Map MobSMS status values to the internal status vocabulary.
     * MobSMS statuses: pending, sent, delivered, failed, cancelled, received.
     */
    private function mapStatus(string $status): string
    {
        return match (strtolower($status)) {
            'pending' => 'queued',
            'sent' => 'sent',
            'delivered', 'received' => 'delivered',
            'failed', 'cancelled' => 'failed',
            default => 'unknown',
        };
    }
}
