<?php

namespace App\Jobs;

use App\Models\Message;
use App\Services\SmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $messageId,
        private array $options = []
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        $message = Message::find($this->messageId);

        if (!$message) {
            Log::error('Message not found for SMS job', ['message_id' => $this->messageId]);
            return;
        }

        // Atomically claim the message by transitioning queued -> processing.
        // If another worker (cron re-dispatch, retry, parallel worker) already
        // claimed it, $claimed will be 0 and we bail out - preventing duplicate sends.
        $claimed = Message::where('id', $this->messageId)
            ->where('status', 'queued')
            ->update(['status' => 'processing']);

        if (!$claimed) {
            Log::info('SMS job skipped - message already claimed by another worker', [
                'message_id' => $this->messageId,
            ]);
            return;
        }

        $message->refresh();

        // Send SMS (no project needed in new system)
        $result = $smsService->sendSms(
            $message->to,
            $message->from,
            $message->text,
            $this->options
        );

        // Update message with result
        $message->update([
            'status' => $result['status'],
            'provider_message_id' => $result['message_id'] ?? null,
            'error_code' => $result['error'] ?? null,
            'error_message' => $result['error'] ?? null,
            'price_decimal' => $result['cost'] ?? null,
            'currency' => $result['currency'] ?? 'UZS',
        ]);

        if ($result['status'] === 'sent' && isset($result['provider_id'])) {
            $message->update(['provider_id' => $result['provider_id']]);
        }

        Log::info('SMS job completed', [
            'message_id' => $this->messageId,
            'status' => $result['status'],
            'provider' => $result['provider_name'] ?? null,
        ]);
    }
}
