<?php

namespace App\Providers\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlayMobileProvider implements SmsProviderInterface
{
    private string $username;
    private string $password;
    private string $baseUrl = 'https://send.smsxabar.uz/broker-api/send';

    public function __construct(array $config)
    {
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
    }

    public function send(string $to, string $from, string $text, array $options = []): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl, [
                'messages' => [
                    [
                        'recipient' => $to,
                        'message-id' => $options['message_id'] ?? uniqid(),
                        'sms' => [
                            'originator' => $from,
                            'content' => [
                                'text' => $text,
                            ],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $message = $data['messages'][0] ?? null;

                if ($message && $message['status']['group-name'] === 'PENDING') {
                    return [
                        'status' => 'sent',
                        'message_id' => $message['message-id'] ?? null,
                        'cost' => 0, // PlayMobile doesn't provide cost in response
                        'currency' => 'UZS',
                        'provider_response' => $data,
                    ];
                }

                return [
                    'status' => 'failed',
                    'error' => $message['status']['description'] ?? 'Unknown error',
                    'provider_response' => $data,
                ];
            }

            Log::error('PlayMobile SMS send failed', [
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return [
                'status' => 'failed',
                'error' => 'HTTP request failed',
                'provider_response' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('PlayMobile SMS exception', [
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
        // PlayMobile doesn't provide status check endpoint in basic API
        // This would need to be implemented with their extended API
        return [
            'status' => 'unknown',
            'error' => 'Status check not implemented for PlayMobile',
        ];
    }

    public function getCapabilities(): array
    {
        return [
            'dlr' => false,     // No delivery reports in basic API
            'unicode' => true,  // Unicode support
            'concat' => true,   // Concatenated messages
            'flash' => false,   // Flash messages
        ];
    }

    public function getName(): string
    {
        return 'playmobile';
    }

    public function validateConfig(array $config): bool
    {
        return !empty($config['username']) && !empty($config['password']);
    }
}
