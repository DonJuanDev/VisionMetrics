<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Webhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TriggerWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60]; // seconds

    protected Webhook $webhook;
    protected string $event;
    protected array $data;

    public function __construct(Webhook $webhook, string $event, array $data)
    {
        $this->webhook = $webhook;
        $this->event = $event;
        $this->data = $data;
    }

    public function handle(): void
    {
        try {
            Log::info('Triggering webhook', [
                'webhook_id' => $this->webhook->id,
                'event' => $this->event,
                'url' => $this->webhook->url,
            ]);

            $payload = [
                'event' => $this->event,
                'timestamp' => now()->toISOString(),
                'company_id' => $this->webhook->company_id,
                'webhook_id' => $this->webhook->id,
                'data' => $this->data,
            ];

            $payloadJson = json_encode($payload);
            $signature = $this->webhook->generateSignature($payloadJson);

            $response = Http::timeout($this->webhook->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Event' => $this->event,
                    'User-Agent' => 'VisionMetrics-Webhook/1.0',
                ])
                ->post($this->webhook->url, $payload);

            if ($response->successful()) {
                $this->webhook->updateStatus('success');
                
                Log::info('Webhook triggered successfully', [
                    'webhook_id' => $this->webhook->id,
                    'status_code' => $response->status(),
                ]);
            } else {
                throw new \Exception("HTTP {$response->status()}: {$response->body()}");
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $error = "Connection timeout: {$e->getMessage()}";
            $this->webhook->updateStatus('timeout', $error);
            
            Log::warning('Webhook connection timeout', [
                'webhook_id' => $this->webhook->id,
                'error' => $error,
                'attempt' => $this->attempts(),
            ]);

            throw $e;

        } catch (\Exception $e) {
            $error = "Request failed: {$e->getMessage()}";
            $this->webhook->updateStatus('failed', $error);
            
            Log::error('Webhook trigger failed', [
                'webhook_id' => $this->webhook->id,
                'error' => $error,
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Webhook job failed permanently', [
            'webhook_id' => $this->webhook->id,
            'event' => $this->event,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        $this->webhook->updateStatus('failed', "Job failed after {$this->attempts()} attempts: {$exception->getMessage()}");
    }
}
