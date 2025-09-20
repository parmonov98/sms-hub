<?php

namespace App\Console\Commands;

use App\Jobs\RefreshProviderTokensJob;
use Illuminate\Console\Command;

class RefreshProviderTokensCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:refresh-provider-tokens 
                            {--sync : Run synchronously instead of dispatching to queue}
                            {--force : Force refresh even if tokens are not expiring soon}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh authentication tokens for all SMS providers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting provider token refresh...');

        if ($this->option('sync')) {
            // Run synchronously
            $this->info('Running token refresh synchronously...');
            $job = new RefreshProviderTokensJob();
            $job->handle(app(\App\Services\ProviderTokenService::class));
            $this->info('Token refresh completed synchronously.');
        } else {
            // Dispatch to queue
            $this->info('Dispatching token refresh job to queue...');
            RefreshProviderTokensJob::dispatch();
            $this->info('Token refresh job dispatched to queue.');
        }

        $this->info('Provider token refresh process initiated successfully.');
        return Command::SUCCESS;
    }
}