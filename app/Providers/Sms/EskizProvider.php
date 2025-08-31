<?php

namespace App\Providers\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EskizProvider implements SmsProviderInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://notify.eskiz.uz/api';

    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? '';
    }

    public function send(string $to, string $from, string $text, array $options = []): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/message/sms/send', [
                'mobile_phone' => $to,
                'message' => $text,
                'from' => $from,
                'callback_url' => $options['callback_url'] ?? null,
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

            Log::error('Eskiz SMS send failed', [
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return [
                'status' => 'failed',
                'error' => $response->json()['message'] ?? 'Unknown error',
                'provider_response' => $response->json(),
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
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/message/sms/status/' . $messageId);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => $this->mapStatus($data['status'] ?? 'unknown'),
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
        return !empty($config['api_key']);
    }

    private function mapStatus(string $eskizStatus): string
    {
        return match ($eskizStatus) {
            'ACCEPTED' => 'sent',
            'DELIVERED' => 'delivered',
            'FAILED' => 'failed',
            'REJECTED' => 'failed',
            default => 'queued',
        };
    }
}
