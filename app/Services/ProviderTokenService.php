<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\ProviderToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProviderTokenService
{
    /**
     * Get or refresh Eskiz token.
     */
    public function getEskizToken(): ?ProviderToken
    {
        $provider = Provider::where('display_name', 'eskiz')->first();
        
        if (!$provider) {
            Log::error('Eskiz provider not found');
            return null;
        }

        // Check if we have a valid token
        $existingToken = $provider->accessToken;
        if ($existingToken && $existingToken->isValid()) {
            return $existingToken;
        }

        // Get new token from Eskiz API
        return $this->refreshEskizToken($provider);
    }

    /**
     * Check if Eskiz token needs refresh (expiring within 2 days).
     */
    public function eskizTokenNeedsRefresh(): bool
    {
        $provider = Provider::where('display_name', 'eskiz')->first();
        
        if (!$provider) {
            return false;
        }

        $token = $provider->accessToken;
        
        if (!$token) {
            return true; // No token exists, needs refresh
        }

        // Check if token is expired or expiring within 2 days
        return $token->isExpired() || 
               ($token->expires_at && $token->expires_at->isBefore(now()->addDays(2)));
    }

    /**
     * Refresh Eskiz token by authenticating with email/password.
     */
    public function refreshEskizToken(Provider $provider): ?ProviderToken
    {
        try {
            $email = env('ESKIZ_EMAIL');
            $password = env('ESKIZ_PASSWORD');

            if (!$email || !$password) {
                Log::error('Eskiz credentials not configured');
                return null;
            }

            $response = Http::asForm()->post('https://notify.eskiz.uz/api/auth/login', [
                'email' => $email,
                'password' => $password,
            ]);

            if (!$response->successful()) {
                $errorMessage = $response->json()['message'] ?? 'Unknown error';
                $statusCode = $response->status();

                // Handle specific authentication errors
                if ($statusCode === 401) {
                    if (str_contains($errorMessage, 'Неверный Email или пароль')) {
                        $errorMessage = 'Invalid Eskiz credentials. Please check your email and password in .env file.';
                    } elseif (str_contains($errorMessage, 'role not found')) {
                        $errorMessage = 'Account does not have SMS sending permissions. Please contact Eskiz support to activate SMS functionality.';
                    }
                }

                Log::error('Eskiz authentication failed', [
                    'response' => $response->json(),
                    'status' => $statusCode,
                    'error_message' => $errorMessage,
                    'email' => $email,
                ]);
                return null;
            }

            $data = $response->json();
            $token = $data['data']['token'] ?? null;

            if (!$token) {
                Log::error('No token in Eskiz response', ['response' => $data]);
                return null;
            }

            // Store the token
            $providerToken = ProviderToken::create([
                'provider_id' => $provider->id,
                'token_type' => 'access',
                'token_value' => $token,
                'expires_at' => now()->addDays(30), // Eskiz tokens last 30 days
                'metadata' => [
                    'email' => $email,
                    'authenticated_at' => now()->toISOString(),
                ],
                'is_active' => true,
            ]);

            // Deactivate old tokens
            ProviderToken::where('provider_id', $provider->id)
                ->where('token_type', 'access')
                ->where('id', '!=', $providerToken->id)
                ->update(['is_active' => false]);

            Log::info('Eskiz token refreshed successfully', [
                'provider_id' => $provider->id,
                'token_id' => $providerToken->id,
            ]);

            return $providerToken;

        } catch (\Exception $e) {
            Log::error('Eskiz token refresh failed', [
                'error' => $e->getMessage(),
                'provider_id' => $provider->id,
            ]);
            return null;
        }
    }


    /**
     * Get all tokens that need refresh (expiring within 2 days).
     */
    public function getTokensNeedingRefresh(): array
    {
        $tokensNeedingRefresh = [];

        if ($this->eskizTokenNeedsRefresh()) {
            $tokensNeedingRefresh[] = [
                'provider' => 'eskiz',
                'provider_id' => Provider::where('display_name', 'eskiz')->first()?->id,
                'reason' => 'expiring_soon_or_expired',
            ];
        }

        return $tokensNeedingRefresh;
    }

    /**
     * Refresh all provider tokens.
     */
    public function refreshAllTokens(): array
    {
        $results = [];

        $eskizToken = $this->refreshEskizToken(Provider::where('display_name', 'eskiz')->first());
        $results['eskiz'] = $eskizToken ? 'success' : 'failed';

        return $results;
    }

    /**
     * Refresh only tokens that need refresh.
     */
    public function refreshTokensNeedingRefresh(): array
    {
        $results = [];
        $tokensNeedingRefresh = $this->getTokensNeedingRefresh();

        foreach ($tokensNeedingRefresh as $tokenInfo) {
            $provider = Provider::find($tokenInfo['provider_id']);
            
            if (!$provider) {
                $results[$tokenInfo['provider']] = [
                    'status' => 'failed',
                    'error' => 'Provider not found',
                ];
                continue;
            }

            switch ($tokenInfo['provider']) {
                case 'eskiz':
                    $token = $this->refreshEskizToken($provider);
                    $results['eskiz'] = $token ? 'success' : 'failed';
                    break;
                default:
                    $results[$tokenInfo['provider']] = [
                        'status' => 'skipped',
                        'error' => 'No refresh logic implemented',
                    ];
            }
        }

        return $results;
    }

    /**
     * Clean up expired tokens.
     */
    public function cleanupExpiredTokens(): int
    {
        return ProviderToken::where('expires_at', '<', now())
            ->update(['is_active' => false]);
    }
}