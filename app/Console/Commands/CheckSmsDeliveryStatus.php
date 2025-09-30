<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckSmsDeliveryStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:check-delivery {--limit=50 : Maximum number of messages to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check delivery status of sent SMS messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        
        $this->info("Checking delivery status for up to {$limit} sent messages...");
        
        // Get messages that are sent but not yet delivered or failed
        $messages = Message::where('status', 'sent')
            ->whereNotNull('provider_message_id')
            ->whereNotNull('provider_id')
            ->where('created_at', '>=', now()->subDays(7)) // Only check messages from last 7 days
            ->limit($limit)
            ->get();
        
        if ($messages->isEmpty()) {
            $this->info('No sent messages found to check.');
            return 0;
        }
        
        $this->info("Found {$messages->count()} messages to check.");
        
        $smsService = app(SmsService::class);
        $updated = 0;
        $errors = 0;
        
        foreach ($messages as $message) {
            try {
                $this->line("Checking message ID: {$message->id} (Provider ID: {$message->provider_message_id})");
                
                $result = $smsService->checkStatus($message->provider_message_id, $message->provider_id);
                
                if ($result['status'] !== 'unknown') {
                    $oldStatus = $message->status;
                    
                    // Prepare update data
                    $updateData = ['status' => $result['status']];
                    
                    // Extract price information from provider response
                    if (isset($result['provider_response']['data'])) {
                        $providerData = $result['provider_response']['data'];
                        
                        if (isset($providerData['price'])) {
                            $updateData['price_decimal'] = $providerData['price'];
                        }
                        
                        if (isset($providerData['total_price'])) {
                            $updateData['price_decimal'] = $providerData['total_price'];
                        }
                        
                        // Set currency to UZS for Eskiz
                        if (isset($providerData['price']) || isset($providerData['total_price'])) {
                            $updateData['currency'] = 'UZS';
                        }
                    }
                    
                    $message->update($updateData);
                    
                    $priceInfo = isset($updateData['price_decimal']) ? " (Price: {$updateData['price_decimal']} {$updateData['currency']})" : '';
                    $this->info("  Status updated: {$oldStatus} → {$result['status']}{$priceInfo}");
                    $updated++;
                    
                    Log::info('SMS delivery status updated', [
                        'message_id' => $message->id,
                        'provider_message_id' => $message->provider_message_id,
                        'old_status' => $oldStatus,
                        'new_status' => $result['status'],
                        'price' => $updateData['price_decimal'] ?? null,
                        'currency' => $updateData['currency'] ?? null,
                        'provider_response' => $result['provider_response'] ?? null,
                    ]);
                } else {
                    $errorMessage = $result['error'] ?? 'No error message';
                    $this->warn("  Status unknown: {$errorMessage}");
                }
                
            } catch (\Exception $e) {
                $this->error("  Error checking message {$message->id}: {$e->getMessage()}");
                $errors++;
                
                Log::error('SMS delivery status check failed', [
                    'message_id' => $message->id,
                    'provider_message_id' => $message->provider_message_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $this->info("Delivery status check completed:");
        $this->info("  - Messages updated: {$updated}");
        $this->info("  - Errors: {$errors}");
        
        return 0;
    }
}
