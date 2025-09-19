<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email', 
        'phone',
        'cnpj',
        'timezone',
        'trial_expires_at',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'trial_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Relacionamentos
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(Conversion::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    public function trackingLinks(): HasMany
    {
        return $this->hasMany(TrackingLink::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTrialExpired($query)
    {
        return $query->where('trial_expires_at', '<', now());
    }

    public function scopeTrialActive($query)
    {
        return $query->where(function ($q) {
            $q->where('trial_expires_at', '>', now())
              ->orWhereNull('trial_expires_at');
        });
    }

    /**
     * Métodos auxiliares
     */
    public function isTrialExpired(): bool
    {
        return $this->trial_expires_at && $this->trial_expires_at->isPast();
    }

    public function isTrialActive(): bool
    {
        return !$this->trial_expires_at || $this->trial_expires_at->isFuture();
    }

    public function getRemainingTrialDays(): int
    {
        if (!$this->trial_expires_at) {
            return 0;
        }

        $days = now()->diffInDays($this->trial_expires_at, false);
        return max(0, $days);
    }

    public function extendTrial(int $days): self
    {
        $newExpiryDate = $this->trial_expires_at 
            ? $this->trial_expires_at->addDays($days)
            : now()->addDays($days);

        $this->update(['trial_expires_at' => $newExpiryDate]);
        
        return $this;
    }

    public function getWhatsAppSupportUrl(): string
    {
        $number = config('app.whatsapp_support.number');
        $message = config('app.whatsapp_support.message');
        $companyName = urlencode($this->name);
        
        $fullMessage = "{$message} Empresa: {$companyName}";
        
        return "https://wa.me/{$number}?text=" . urlencode($fullMessage);
    }

    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function setSetting(string $key, $value): self
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
        
        return $this;
    }
}
