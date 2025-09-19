<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TrackingLink extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'token',
        'name',
        'destination_url',
        'utm_source',
        'utm_campaign',
        'utm_medium',
        'utm_term',
        'utm_content',
        'is_active',
        'click_count',
        'conversion_count',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Relacionamentos
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeByToken($query, $token)
    {
        return $query->where('token', $token);
    }

    /**
     * Métodos auxiliares
     */
    public function isActive(): bool
    {
        return $this->is_active && (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function incrementClicks(): void
    {
        $this->increment('click_count');
    }

    public function incrementConversions(): void
    {
        $this->increment('conversion_count');
    }

    public function getConversionRate(): float
    {
        if ($this->click_count === 0) {
            return 0;
        }

        return ($this->conversion_count / $this->click_count) * 100;
    }

    public function generateTrackingUrl(): string
    {
        $baseUrl = config('app.url');
        $prefix = config('tracking.link_prefix', 'r');
        
        return "{$baseUrl}/{$prefix}/{$this->token}";
    }

    public function getDestinationUrlWithUtm(): string
    {
        $url = $this->destination_url;
        $utmParams = $this->getUtmParameters();

        if (empty($utmParams)) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';
        $queryString = http_build_query($utmParams);

        return $url . $separator . $queryString;
    }

    public function getUtmParameters(): array
    {
        $params = [];

        if ($this->utm_source) $params['utm_source'] = $this->utm_source;
        if ($this->utm_campaign) $params['utm_campaign'] = $this->utm_campaign;
        if ($this->utm_medium) $params['utm_medium'] = $this->utm_medium;
        if ($this->utm_term) $params['utm_term'] = $this->utm_term;
        if ($this->utm_content) $params['utm_content'] = $this->utm_content;

        return $params;
    }

    public function getOrigin(): string
    {
        if (!$this->utm_source) {
            return 'outras';
        }

        $source = strtolower($this->utm_source);

        if (in_array($source, ['facebook', 'instagram', 'meta'])) {
            return 'meta';
        }

        if (in_array($source, ['google', 'googleads'])) {
            return 'google';
        }

        return 'outras';
    }

    /**
     * Geração de token único
     */
    public static function generateUniqueToken(int $length = null): string
    {
        $length = $length ?: config('tracking.token_length', 8);
        
        do {
            $token = Str::random($length);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Criação de link rastreável
     */
    public static function createLink(array $data): self
    {
        $data['token'] = self::generateUniqueToken();
        
        if (!isset($data['created_by']) && auth()->check()) {
            $data['created_by'] = auth()->id();
        }

        return self::create($data);
    }

    /**
     * QR Code
     */
    public function getQrCodeUrl(): string
    {
        $trackingUrl = urlencode($this->generateTrackingUrl());
        return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$trackingUrl}";
    }

    /**
     * Estatísticas
     */
    public function getClicksByDay(int $days = 30): array
    {
        // Esta implementação seria mais complexa com uma tabela de clicks separada
        // Por simplicidade, retornamos dados mock
        return [];
    }

    public function getStats(): array
    {
        return [
            'clicks' => $this->click_count,
            'conversions' => $this->conversion_count,
            'conversion_rate' => $this->getConversionRate(),
            'is_active' => $this->isActive(),
            'is_expired' => $this->isExpired(),
            'days_active' => $this->created_at->diffInDays(now()),
            'origin' => $this->getOrigin(),
        ];
    }

    /**
     * Boot method
     */
    protected static function booted()
    {
        static::creating(function ($link) {
            if (!$link->token) {
                $link->token = self::generateUniqueToken();
            }
        });

        static::created(function ($link) {
            AuditLog::create([
                'company_id' => $link->company_id,
                'user_id' => $link->created_by,
                'event' => 'tracking_link.created',
                'auditable_type' => self::class,
                'auditable_id' => $link->id,
                'new_values' => $link->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        static::updated(function ($link) {
            if ($link->isDirty()) {
                AuditLog::create([
                    'company_id' => $link->company_id,
                    'user_id' => auth()->id(),
                    'event' => 'tracking_link.updated',
                    'auditable_type' => self::class,
                    'auditable_id' => $link->id,
                    'old_values' => $link->getOriginal(),
                    'new_values' => $link->getChanges(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });
    }
}
