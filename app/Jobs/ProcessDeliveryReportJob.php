<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDeliveryReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $providerName,
        private array $data
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $messageId = $this->data['message_id'] ?? null;
        $status = $this->data['status'] ?? null;

        if (!$messageId || !$status) {
            Log::warning('Invalid delivery report data', [
                'provider' => $this->providerName,
                'data' => $this->data,
            ]);
            return;
        }

        // Find message by provider message ID
        $message = Message::where('provider_message_id', $messageId)->first();

        if (!$message) {
            Log::warning('Message not found for delivery report', [
                'provider' => $this->providerName,
                'message_id' => $messageId,
            ]);
            return;
        }

        // Map provider status to our status
        $mappedStatus = $this->mapStatus($status);

        // Update message status
        $message->update(['status' => $mappedStatus]);

        Log::info('Delivery report processed', [
            'message_id' => $message->id,
            'provider' => $this->providerName,
            'provider_status' => $status,
            'mapped_status' => $mappedStatus,
        ]);
    }

    private function mapStatus(string $providerStatus): string
    {
        return match (strtolower($providerStatus)) {
            'delivered', 'success' => 'delivered',
            'failed', 'error', 'rejected' => 'failed',
            'sent', 'accepted' => 'sent',
            default => 'queued',
        };
    }
}
