<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'company_id', 
        'lead_id',
        'value',
        'currency',
        'payment_method',
        'detected_by',
        'detection_data',
        'status',
        'confirmed_by',
        'confirmed_at',
        'detected_at',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'detection_data' => 'array',
        'confirmed_at' => 'datetime',
        'detected_at' => 'datetime',
    ];

    /**
     * Relacionamentos
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Scopes
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeDetectedBy($query, $detector)
    {
        return $query->where('detected_by', $detector);
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('confirmed_at', [$startDate, $endDate]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('confirmed_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('confirmed_at', today());
    }

    /**
     * Métodos auxiliares
     */
    public function getStatusName(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'confirmed' => 'Confirmada',
            'cancelled' => 'Cancelada',
            default => $this->status,
        };
    }

    public function getPaymentMethodName(): ?string
    {
        return match($this->payment_method) {
            'pix' => 'PIX',
            'boleto' => 'Boleto',
            'cartao_credito' => 'Cartão de Crédito',
            'cartao_debito' => 'Cartão de Débito',
            'transferencia' => 'Transferência',
            'dinheiro' => 'Dinheiro',
            'outro' => 'Outro',
            default => $this->payment_method,
        };
    }

    public function getDetectedByName(): string
    {
        return match($this->detected_by) {
            'manual' => 'Manual',
            'nlp' => 'Automático (NLP)',
            'webhook' => 'Webhook',
            default => $this->detected_by,
        };
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function confirm(User $user, string $notes = null): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_by' => $user->id,
            'confirmed_at' => now(),
            'notes' => $notes,
        ]);

        // Marca conversa e lead como convertidos
        $this->conversation->markAsConverted();
        
        // Dispara eventos/webhooks
        $this->triggerWebhooks();
        $this->sendToAdvertisingPlatforms();
    }

    public function cancel(User $user, string $notes = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'confirmed_by' => $user->id,
            'confirmed_at' => now(),
            'notes' => $notes,
        ]);
    }

    public function getFormattedValue(): string
    {
        return 'R$ ' . number_format($this->value, 2, ',', '.');
    }

    public function getConfidence(): float
    {
        return $this->detection_data['confidence'] ?? 0;
    }

    /**
     * Integração com plataformas de anúncios
     */
    private function sendToAdvertisingPlatforms(): void
    {
        // Meta Conversions API
        if ($this->lead->origin === 'meta' && config('tracking.meta_conversions.pixel_id')) {
            dispatch(new \App\Jobs\SendConversionToMeta($this));
        }

        // Google Ads
        if ($this->lead->origin === 'google' && config('tracking.google_ads.customer_id')) {
            dispatch(new \App\Jobs\SendConversionToGoogle($this));
        }
    }

    private function triggerWebhooks(): void
    {
        $webhooks = $this->company->webhooks()
            ->where('active', true)
            ->get();

        foreach ($webhooks as $webhook) {
            $events = $webhook->events ?? [];
            
            if (in_array('conversion.confirmed', $events)) {
                dispatch(new \App\Jobs\TriggerWebhook($webhook, 'conversion.confirmed', $this->toArray()));
            }
        }
    }

    /**
     * Boot method
     */
    protected static function booted()
    {
        static::created(function ($conversion) {
            // Log de auditoria
            AuditLog::create([
                'company_id' => $conversion->company_id,
                'user_id' => auth()->id(),
                'event' => 'conversion.created',
                'auditable_type' => self::class,
                'auditable_id' => $conversion->id,
                'new_values' => $conversion->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        static::updated(function ($conversion) {
            if ($conversion->wasChanged('status') && $conversion->status === 'confirmed') {
                // Log de auditoria para confirmação
                AuditLog::create([
                    'company_id' => $conversion->company_id,
                    'user_id' => $conversion->confirmed_by,
                    'event' => 'conversion.confirmed',
                    'auditable_type' => self::class,
                    'auditable_id' => $conversion->id,
                    'old_values' => $conversion->getOriginal(),
                    'new_values' => $conversion->getChanges(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });
    }
}
