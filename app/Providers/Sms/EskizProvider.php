<?php

namespace App\Providers\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EskizProvider implements SmsProviderInterface
{
    private string $token;
    private string $baseUrl = 'https://notify.eskiz.uz/api';

    public function __construct(array $config)
    {
        $this->token = $config['token'] ?? '';
    }

    public function send(string $to, string $from, string $text, array $options = []): array
    {
        try {
            if (!$this->token) {
                return [
                    'status' => 'failed',
                    'error' => 'No authentication token provided',
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])->asForm()->post($this->baseUrl . '/message/sms/send', [
                'mobile_phone' => $to,
                'message' => $text,
                'from' => $from,
                'callback_url' => $options['callback_url'] ?? url('/api/v1/sms/delivery-callback'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => 'sent',
                    'message_id' => $data['id'] ?? null,
                    'cost' => $data['price'] ?? 0,
                    'currency' => 'UZS',
                    'provider_response' => $data,
                ];
            }

            $errorMessage = $response->json()['message'] ?? 'Unknown error';
            $statusCode = $response->status();

            // Handle specific Eskiz API errors
            if ($statusCode === 401) {
                if (str_contains($errorMessage, 'role not found')) {
                    $errorMessage = 'Account does not have SMS sending permissions. Please contact Eskiz support to activate SMS functionality.';
                } elseif (str_contains($errorMessage, 'Неверный Email или пароль')) {
                    $errorMessage = 'Invalid Eskiz credentials. Please check your email and password.';
                } elseif (str_contains($errorMessage, 'Method error')) {
                    $errorMessage = 'Invalid API method or endpoint. Please check Eskiz API documentation.';
                }
            }

            Log::error('Eskiz SMS send failed', [
                'response' => $response->json(),
                'status' => $statusCode,
                'error_message' => $errorMessage,
                'to' => $to,
                'from' => $from,
            ]);

            return [
                'status' => 'failed',
                'error' => $errorMessage,
                'provider_response' => $response->json(),
                'status_code' => $statusCode,
            ];

        } catch (\Exception $e) {
            Log::error('Eskiz SMS exception', [
                'error' => $e->getMessage(),
                'to' => $to,
                'from' => $from,
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
            if (!$this->token) {
                return [
                    'status' => 'unknown',
                    'error' => 'No authentication token provided',
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])->get($this->baseUrl . '/message/sms/status_by_id/' . $messageId);

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['data']['status'] ?? $data['status'] ?? 'unknown';
                return [
                    'status' => $this->mapStatus($status),
                    'provider_response' => $data,
                ];
            }

            return [
                'status' => 'unknown',
                'error' => 'Failed to check status',
                'provider_response' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('Eskiz SMS status check failed', [
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
            'dlr' => true,      // Delivery reports
            'unicode' => true,  // Unicode support
            'concat' => true,   // Concatenated messages
            'flash' => false,   // Flash messages
        ];
    }

    public function getName(): string
    {
        return 'eskiz';
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['token']);
    }

    private function mapStatus(string $eskizStatus): string
    {
        return match (strtoupper($eskizStatus)) {
            'ACCEPTED' => 'sent',
            'DELIVERED' => 'delivered',
            'FAILED' => 'failed',
            'REJECTED' => 'failed',
            'EXPIRED' => 'failed',
            'PENDING' => 'queued',
            'SENT' => 'sent',
            default => 'unknown',
        };
    }
}