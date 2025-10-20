<?php

namespace App\Console\Commands;

use App\Jobs\SendSmsJob;
use App\Models\Message;
use Illuminate\Console\Command;

class ProcessQueuedMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:process-queued {--limit=100 : Maximum number of messages to process} {--dry-run : Show what would be processed without actually dispatching jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch SendSmsJob for all queued SMS messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $dryRun = $this->option('dry-run');

        $this->info("Processing queued SMS messages...");
        $this->info("Limit: {$limit}");

        if ($dryRun) {
            $this->warn("DRY RUN MODE - No jobs will be dispatched");
        }

        // Get queued messages
        $queuedMessages = Message::where('status', 'queued')
            ->limit($limit)
            ->get();

        if ($queuedMessages->isEmpty()) {
            $this->info("No queued messages found.");
            return 0;
        }

        $this->info("Found {$queuedMessages->count()} queued messages");

        $dispatched = 0;
        $errors = 0;

        foreach ($queuedMessages as $message) {
            try {
                if (!$dryRun) {
                    SendSmsJob::dispatch($message->id);
                    $dispatched++;
                }

                $this->line("Message ID {$message->id}: {$message->to} - " . ($dryRun ? "Would dispatch" : "Dispatched"));
            } catch (\Exception $e) {
                $errors++;
                $this->error("Failed to dispatch Message ID {$message->id}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("- Total queued messages: {$queuedMessages->count()}");

        if ($dryRun) {
            $this->info("- Would dispatch: {$queuedMessages->count()}");
        } else {
            $this->info("- Successfully dispatched: {$dispatched}");
        }

        if ($errors > 0) {
            $this->error("- Errors: {$errors}");
        }

        return 0;
    }
}
