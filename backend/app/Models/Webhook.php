<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Webhook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'url',
        'events',
        'secret',
        'active',
        'retry_attempts',
        'timeout',
        'last_triggered_at',
        'last_status',
        'last_error',
    ];

    protected $casts = [
        'events' => 'array',
        'active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    /**
     * Relacionamentos
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scopes
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForEvent($query, $event)
    {
        return $query->whereJsonContains('events', $event);
    }

    /**
     * Métodos auxiliares
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    public function activate(): void
    {
        $this->update(['active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['active' => false]);
    }

    public function subscribesToEvent(string $event): bool
    {
        return in_array($event, $this->events ?? []);
    }

    public function addEvent(string $event): void
    {
        $events = $this->events ?? [];
        if (!in_array($event, $events)) {
            $events[] = $event;
            $this->update(['events' => $events]);
        }
    }

    public function removeEvent(string $event): void
    {
        $events = $this->events ?? [];
        $events = array_filter($events, fn($e) => $e !== $event);
        $this->update(['events' => array_values($events)]);
    }

    public function getAvailableEvents(): array
    {
        return config('tracking.webhooks.events', [
            'lead.created',
            'conversation.started',
            'conversion.detected',
            'conversion.confirmed',
            'trial.expired',
        ]);
    }

    public function updateStatus(string $status, string $error = null): void
    {
        $this->update([
            'last_triggered_at' => now(),
            'last_status' => $status,
            'last_error' => $error,
        ]);
    }

    public function generateSignature(string $payload): string
    {
        if (!$this->secret) {
            return '';
        }

        return hash_hmac('sha256', $payload, $this->secret);
    }

    public function verifySignature(string $payload, string $signature): bool
    {
        if (!$this->secret) {
            return true; // Se não há secret, não verifica
        }

        $expectedSignature = $this->generateSignature($payload);
        return hash_equals($expectedSignature, $signature);
    }

    public function getLastStatusColor(): string
    {
        return match($this->last_status) {
            'success' => 'green',
            'failed' => 'red',
            'timeout' => 'orange',
            default => 'gray',
        };
    }

    public function getLastStatusName(): string
    {
        return match($this->last_status) {
            'success' => 'Sucesso',
            'failed' => 'Falhou',
            'timeout' => 'Timeout',
            default => 'Nunca executado',
        };
    }

    /**
     * Teste de webhook
     */
    public function test(): array
    {
        $testPayload = [
            'event' => 'webhook.test',
            'timestamp' => now()->toISOString(),
            'data' => [
                'webhook_id' => $this->id,
                'message' => 'Este é um teste de webhook',
            ],
        ];

        return $this->trigger('webhook.test', $testPayload);
    }

    public function trigger(string $event, array $data): array
    {
        if (!$this->isActive() || !$this->subscribesToEvent($event)) {
            return [
                'success' => false,
                'error' => 'Webhook inativo ou evento não subscrito',
            ];
        }

        $payload = [
            'event' => $event,
            'timestamp' => now()->toISOString(),
            'company_id' => $this->company_id,
            'webhook_id' => $this->id,
            'data' => $data,
        ];

        $payloadJson = json_encode($payload);
        $signature = $this->generateSignature($payloadJson);

        try {
            $response = \Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Event' => $event,
                    'User-Agent' => 'VisionMetrics-Webhook/1.0',
                ])
                ->post($this->url, $payload);

            if ($response->successful()) {
                $this->updateStatus('success');
                return ['success' => true];
            } else {
                $error = "HTTP {$response->status()}: {$response->body()}";
                $this->updateStatus('failed', $error);
                return ['success' => false, 'error' => $error];
            }

        } catch (\Illuminate\Http\Client\RequestException $e) {
            $error = "Request failed: {$e->getMessage()}";
            $this->updateStatus('failed', $error);
            return ['success' => false, 'error' => $error];

        } catch (\Illuminate\Http\Client\ConnectException $e) {
            $error = "Connection timeout: {$e->getMessage()}";
            $this->updateStatus('timeout', $error);
            return ['success' => false, 'error' => $error];

        } catch (\Exception $e) {
            $error = "Unexpected error: {$e->getMessage()}";
            $this->updateStatus('failed', $error);
            return ['success' => false, 'error' => $error];
        }
    }

    /**
     * Boot method
     */
    protected static function booted()
    {
        static::created(function ($webhook) {
            AuditLog::create([
                'company_id' => $webhook->company_id,
                'user_id' => auth()->id(),
                'event' => 'webhook.created',
                'auditable_type' => self::class,
                'auditable_id' => $webhook->id,
                'new_values' => $webhook->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        static::updated(function ($webhook) {
            if ($webhook->isDirty()) {
                AuditLog::create([
                    'company_id' => $webhook->company_id,
                    'user_id' => auth()->id(),
                    'event' => 'webhook.updated',
                    'auditable_type' => self::class,
                    'auditable_id' => $webhook->id,
                    'old_values' => $webhook->getOriginal(),
                    'new_values' => $webhook->getChanges(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });
    }
}
