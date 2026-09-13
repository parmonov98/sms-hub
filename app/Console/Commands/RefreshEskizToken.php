<?php

namespace App\Console\Commands;

use App\Models\Provider;
use App\Services\ProviderTokenService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshEskizToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eskiz:refresh-token
                            {--show-token : Display the token value}
                            {--force : Force refresh even if token is valid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Eskiz SMS provider authentication token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Refreshing Eskiz token...');

        // Get Eskiz provider
        $provider = Provider::where('display_name', 'eskiz')->first();

        if (!$provider) {
            $this->error('❌ Eskiz provider not found in database!');
            return Command::FAILURE;
        }

        $this->info("📱 Provider: {$provider->display_name}");
        $this->info("🆔 Provider ID: {$provider->id}");

        // Check current token status
        $currentToken = $provider->accessToken;
        if ($currentToken) {
            $this->info("📋 Current token status:");
            $this->info("   - Token ID: {$currentToken->id}");
            $this->info("   - Valid: " . ($currentToken->isValid() ? '✅ Yes' : '❌ No'));
            $this->info("   - Expires: {$currentToken->expires_at}");

            if (!$this->option('force') && $currentToken->isValid()) {
                $this->warn('⚠️  Token is still valid. Use --force to refresh anyway.');
                if ($this->option('show-token')) {
                    $this->info("🔑 Token: {$currentToken->token_value}");
                }
                return Command::SUCCESS;
            }
        } else {
            $this->info("📋 No current token found");
        }

        // Check configured credentials (cache-safe: works with config:cache)
        $email = config('services.eskiz.email');
        $password = config('services.eskiz.password');

        if (!$email || !$password) {
            $this->error('❌ Eskiz credentials not configured!');
            $this->error('   Please set ESKIZ_EMAIL and ESKIZ_PASSWORD in your .env file');
            return Command::FAILURE;
        }

        $this->info("🔐 Credentials configured for: {$email}");

        // Refresh token
        $this->info("🌐 Authenticating with Eskiz API...");

        $tokenService = new ProviderTokenService();
        $newToken = $tokenService->refreshEskizToken($provider);

        if (!$newToken) {
            $this->error('❌ Failed to refresh Eskiz token!');
            $this->error('   Check your credentials and Eskiz account status');
            $this->error('   Check logs: tail -f storage/logs/laravel.log');
            return Command::FAILURE;
        }

        $this->info("✅ Token refreshed successfully!");
        $this->info("🆔 New Token ID: {$newToken->id}");
        $this->info("⏰ Expires: {$newToken->expires_at}");
        $this->info("📅 Valid for: " . $newToken->expires_at->diffInDays(now()) . " days");

        if ($this->option('show-token')) {
            $this->info("🔑 Token: {$newToken->token_value}");
        } else {
            $this->info("🔑 Token: " . substr($newToken->token_value, 0, 30) . "...");
            $this->info("   Use --show-token to display full token");
        }

        $this->info("🎉 Eskiz token refresh completed successfully!");

        return Command::SUCCESS;
    }
}
