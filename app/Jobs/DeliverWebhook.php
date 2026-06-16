<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * POST a JSON payload to an outbound webhook (e.g. a Discord channel), retrying
 * with exponential backoff up to 3 attempts. Used for instance announcement and
 * calendar notifications (the admin-configured webhook URLs).
 */
class DeliverWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $url,
        public array $payload,
        public string $context = 'webhook',
    ) {}

    /**
     * Exponential backoff between attempts (seconds).
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function handle(): void
    {
        Http::asJson()->timeout(10)->post($this->url, $this->payload)->throw();
    }

    public function failed(?Throwable $e): void
    {
        Log::warning('Webhook delivery failed', [
            'context' => $this->context,
            'error' => $e?->getMessage(),
        ]);
    }
}
