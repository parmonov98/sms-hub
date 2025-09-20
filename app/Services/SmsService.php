<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\ProviderToken;
use App\Providers\Sms\EskizProvider;
use App\Providers\Sms\SmsProviderInterface;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private array $providerInstances = [];

    /**
     * Send SMS with failover support.
     */
    public function sendSms(string $to, string $from, string $text, array $options = []): array
    {
        // Get active providers ordered by priority
        $providers = Provider::enabled()
            ->byPriority()
            ->with('accessToken')
            ->get();

        if ($providers->isEmpty()) {
            return [
                'status' => 'failed',
                'error' => 'No active SMS providers configured',
            ];
        }

        // Try each provider in order until one succeeds
        foreach ($providers as $provider) {
            $providerInstance = $this->getProviderInstance($provider);
            
            if (!$providerInstance) {
                Log::warning('Invalid provider configuration', [
                    'provider_id' => $provider->id,
                    'provider_name' => $provider->display_name,
                ]);
                continue;
            }

            $result = $providerInstance->send($to, $from, $text, $options);

            if ($result['status'] === 'sent') {
                Log::info('SMS sent successfully', [
                    'provider_id' => $provider->id,
                    'provider' => $provider->display_name,
                    'to' => $to,
                    'message_id' => $result['message_id'] ?? null,
                ]);

                return array_merge($result, [
                    'provider_id' => $provider->id,
                    'provider_name' => $provider->display_name,
                ]);
            }

            Log::warning('SMS send failed, trying next provider', [
                'provider_id' => $provider->id,
                'provider' => $provider->display_name,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
        }

        // All providers failed
        Log::error('All SMS providers failed', [
            'to' => $to,
            'from' => $from,
        ]);

        return [
            'status' => 'failed',
            'error' => 'All configured SMS providers failed',
        ];
    }

    /**
     * Check message status.
     */
    public function checkStatus(string $messageId, int $providerId): array
    {
        $provider = Provider::find($providerId);

        if (!$provider) {
            return [
                'status' => 'unknown',
                'error' => 'Provider not found',
            ];
        }

        $providerInstance = $this->getProviderInstance($provider);
        
        if (!$providerInstance) {
            return [
                'status' => 'unknown',
                'error' => 'Invalid provider configuration',
            ];
        }

        return $providerInstance->checkStatus($messageId);
    }

    /**
     * Get provider instance with caching.
     */
    private function getProviderInstance(Provider $provider): ?SmsProviderInterface
    {
        $key = $provider->id;

        if (isset($this->providerInstances[$key])) {
            return $this->providerInstances[$key];
        }

        // Get the access token for this provider
        $accessToken = $provider->accessToken;
        
        if (!$accessToken || !$accessToken->isValid()) {
            Log::warning('No valid access token for provider', [
                'provider_id' => $provider->id,
                'provider_name' => $provider->display_name,
            ]);
            return null;
        }

        // Create provider instance with token
        $providerInstance = match ($provider->display_name) {
            'eskiz' => new EskizProvider(['token' => $accessToken->token_value]),
            default => null,
        };

        if ($providerInstance) {
            $this->providerInstances[$key] = $providerInstance;
            return $providerInstance;
        }

        return null;
    }

    /**
     * Get available providers.
     */
    public function getAvailableProviders(): array
    {
        return Provider::enabled()
            ->byPriority()
            ->with('accessToken')
            ->get()
            ->map(function ($provider) {
                return [
                    'id' => $provider->id,
                    'name' => $provider->display_name,
                    'capabilities' => $provider->capabilities,
                    'priority' => $provider->priority,
                    'has_token' => $provider->accessToken && $provider->accessToken->isValid(),
                ];
            })
            ->toArray();
    }

    /**
     * Store or update a provider token.
     */
    public function storeProviderToken(int $providerId, string $tokenType, string $tokenValue, ?\DateTime $expiresAt = null, array $metadata = []): ProviderToken
    {
        // Deactivate existing tokens of the same type
        ProviderToken::where('provider_id', $providerId)
            ->where('token_type', $tokenType)
            ->update(['is_active' => false]);

        // Create new token
        return ProviderToken::create([
            'provider_id' => $providerId,
            'token_type' => $tokenType,
            'token_value' => $tokenValue,
            'expires_at' => $expiresAt,
            'metadata' => $metadata,
            'is_active' => true,
        ]);
    }

    /**
     * Get provider token.
     */
    public function getProviderToken(int $providerId, string $tokenType = 'access'): ?ProviderToken
    {
        return ProviderToken::where('provider_id', $providerId)
            ->where('token_type', $tokenType)
            ->valid()
            ->first();
    }
}