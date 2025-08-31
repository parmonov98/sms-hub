<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\UsageDaily;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AggregateUsageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $date = null
    ) {
        $this->date = $date ?? now()->format('Y-m-d');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Aggregate usage for the specified date
        $usage = DB::table('messages')
            ->select([
                'project_id',
                DB::raw('COUNT(*) as messages'),
                DB::raw('SUM(parts) as parts'),
                DB::raw('SUM(COALESCE(price_decimal, 0)) as cost'),
                DB::raw('MAX(currency) as currency'),
            ])
            ->whereDate('created_at', $this->date)
            ->where('status', 'sent')
            ->groupBy('project_id')
            ->get();

        foreach ($usage as $record) {
            UsageDaily::updateOrCreate(
                [
                    'project_id' => $record->project_id,
                    'date' => $this->date,
                ],
                [
                    'messages' => $record->messages,
                    'parts' => $record->parts,
                    'cost_decimal' => $record->cost,
                    'currency' => $record->currency ?: 'USD',
                ]
            );
        }

        Log::info('Usage aggregation completed', [
            'date' => $this->date,
            'records_processed' => $usage->count(),
        ]);
    }
}
