<?php

namespace App\Jobs;

use App\Models\Provider;
use App\Models\ProviderToken;
use App\Services\ProviderTokenService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefreshProviderTokensJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes timeout
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(ProviderTokenService $tokenService): void
    {
        Log::info('Starting provider token refresh job');

        $results = [];
        $providers = Provider::enabled()->get();

        foreach ($providers as $provider) {
            try {
                Log::info("Refreshing tokens for provider: {$provider->display_name}");

                $result = $this->refreshProviderTokens($provider, $tokenService);
                $results[$provider->display_name] = $result;

                Log::info("Token refresh completed for {$provider->display_name}", $result);

            } catch (\Exception $e) {
                Log::error("Failed to refresh tokens for {$provider->display_name}", [
                    'error' => $e->getMessage(),
                    'provider_id' => $provider->id,
                ]);

                $results[$provider->display_name] = [
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Clean up expired tokens
        $cleanedCount = $tokenService->cleanupExpiredTokens();
        Log::info("Cleaned up {$cleanedCount} expired tokens");

        Log::info('Provider token refresh job completed', [
            'results' => $results,
            'expired_tokens_cleaned' => $cleanedCount,
        ]);
    }

    /**
     * Refresh tokens for a specific provider.
     */
    private function refreshProviderTokens(Provider $provider, ProviderTokenService $tokenService): array
    {
        $result = [
            'provider_id' => $provider->id,
            'provider_name' => $provider->display_name,
            'tokens_refreshed' => 0,
            'tokens_failed' => 0,
            'details' => [],
        ];

        // Check if tokens need refresh (expiring within 2 days or already expired)
        $tokensNeedingRefresh = ProviderToken::where('provider_id', $provider->id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '<=', now()->addDays(2));
            })
            ->get();

        if ($tokensNeedingRefresh->isEmpty()) {
            $result['status'] = 'no_refresh_needed';
            $result['message'] = 'All tokens are still valid';
            return $result;
        }

        // Refresh tokens based on provider type
        switch ($provider->display_name) {
            case 'eskiz':
                $tokenResult = $this->refreshEskizTokens($provider, $tokenService);
                break;
            case 'playmobile':
                $tokenResult = $this->refreshPlayMobileTokens($provider, $tokenService);
                break;
            default:
                $tokenResult = [
                    'status' => 'skipped',
                    'message' => 'No refresh logic implemented for this provider',
                ];
        }

        $result['details'] = $tokenResult;
        $result['status'] = $tokenResult['status'] ?? 'unknown';

        if ($tokenResult['status'] === 'success') {
            $result['tokens_refreshed'] = $tokenResult['tokens_refreshed'] ?? 1;
        } else {
            $result['tokens_failed'] = 1;
        }

        return $result;
    }

    /**
     * Refresh Eskiz tokens.
     */
    private function refreshEskizTokens(Provider $provider, ProviderTokenService $tokenService): array
    {
        $token = $tokenService->getEskizToken();
        
        if ($token) {
            return [
                'status' => 'success',
                'tokens_refreshed' => 1,
                'token_id' => $token->id,
                'expires_at' => $token->expires_at?->toISOString(),
                'message' => 'Eskiz token refreshed successfully',
            ];
        }

        return [
            'status' => 'failed',
            'message' => 'Failed to refresh Eskiz token',
        ];
    }

    /**
     * Refresh PlayMobile tokens.
     */
    private function refreshPlayMobileTokens(Provider $provider, ProviderTokenService $tokenService): array
    {
        $token = $tokenService->getPlayMobileToken();
        
        if ($token) {
            return [
                'status' => 'success',
                'tokens_refreshed' => 1,
                'token_id' => $token->id,
                'expires_at' => $token->expires_at?->toISOString(),
                'message' => 'PlayMobile token refreshed successfully',
            ];
        }

        return [
            'status' => 'failed',
            'message' => 'Failed to refresh PlayMobile token',
        ];
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('RefreshProviderTokensJob failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}