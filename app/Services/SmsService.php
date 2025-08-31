<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Provider;
use App\Models\ProviderCredential;
use App\Providers\Sms\EskizProvider;
use App\Providers\Sms\PlayMobileProvider;
use App\Providers\Sms\SmsProviderInterface;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private array $providerInstances = [];

    /**
     * Send SMS with failover support.
     */
    public function sendSms(Project $project, string $to, string $from, string $text, array $options = []): array
    {
        // Get active providers for this project, ordered by priority
        $credentials = $project->providerCredentials()
            ->with('provider')
            ->active()
            ->get()
            ->sortBy('provider.priority');

        if ($credentials->isEmpty()) {
            return [
                'status' => 'failed',
                'error' => 'No active SMS providers configured for this project',
            ];
        }

        // Try each provider in order until one succeeds
        foreach ($credentials as $credential) {
            $provider = $this->getProviderInstance($credential);
            
            if (!$provider) {
                Log::warning('Invalid provider configuration', [
                    'project_id' => $project->id,
                    'provider_id' => $credential->provider_id,
                ]);
                continue;
            }

            $result = $provider->send($to, $from, $text, $options);

            if ($result['status'] === 'sent') {
                Log::info('SMS sent successfully', [
                    'project_id' => $project->id,
                    'provider' => $credential->provider->display_name,
                    'to' => $to,
                    'message_id' => $result['message_id'] ?? null,
                ]);

                return array_merge($result, [
                    'provider_id' => $credential->provider_id,
                    'provider_name' => $credential->provider->display_name,
                ]);
            }

            Log::warning('SMS send failed, trying next provider', [
                'project_id' => $project->id,
                'provider' => $credential->provider->display_name,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
        }

        // All providers failed
        Log::error('All SMS providers failed', [
            'project_id' => $project->id,
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
    public function checkStatus(Project $project, string $messageId, int $providerId): array
    {
        $credential = $project->providerCredentials()
            ->where('provider_id', $providerId)
            ->active()
            ->first();

        if (!$credential) {
            return [
                'status' => 'unknown',
                'error' => 'Provider not found or inactive',
            ];
        }

        $provider = $this->getProviderInstance($credential);
        
        if (!$provider) {
            return [
                'status' => 'unknown',
                'error' => 'Invalid provider configuration',
            ];
        }

        return $provider->checkStatus($messageId);
    }

    /**
     * Get provider instance with caching.
     */
    private function getProviderInstance(ProviderCredential $credential): ?SmsProviderInterface
    {
        $key = $credential->id;

        if (isset($this->providerInstances[$key])) {
            return $this->providerInstances[$key];
        }

        $config = json_decode($credential->credentials, true);
        
        if (!$config) {
            return null;
        }

        $provider = match ($credential->provider->display_name) {
            'eskiz' => new EskizProvider($config),
            'playmobile' => new PlayMobileProvider($config),
            default => null,
        };

        if ($provider && $provider->validateConfig($config)) {
            $this->providerInstances[$key] = $provider;
            return $provider;
        }

        return null;
    }

    /**
     * Get available providers for a project.
     */
    public function getAvailableProviders(Project $project): array
    {
        return $project->providerCredentials()
            ->with('provider')
            ->active()
            ->get()
            ->map(function ($credential) {
                return [
                    'id' => $credential->provider_id,
                    'name' => $credential->provider->display_name,
                    'capabilities' => $credential->provider->capabilities,
                    'priority' => $credential->provider->priority,
                ];
            })
            ->toArray();
    }
}
