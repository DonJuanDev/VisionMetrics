<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'phone',
        'name',
        'email',
        'first_contact_at',
        'last_message_at',
        'origin',
        'utm_source',
        'utm_campaign',
        'utm_medium',
        'utm_term',
        'utm_content',
        'tracking_token',
        'referrer_url',
        'attribution_data',
        'status',
        'tags',
    ];

    protected $casts = [
        'first_contact_at' => 'datetime',
        'last_message_at' => 'datetime',
        'attribution_data' => 'array',
        'tags' => 'array',
    ];

    /**
     * Relacionamentos
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(Conversion::class);
    }

    /**
     * Scopes
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByOrigin($query, $origin)
    {
        return $query->where('origin', $origin);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeTracked($query)
    {
        return $query->whereIn('origin', ['meta', 'google', 'outras']);
    }

    public function scopeUntracked($query)
    {
        return $query->where('origin', 'nao_rastreada');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Métodos auxiliares
     */
    public function isTracked(): bool
    {
        return in_array($this->origin, ['meta', 'google', 'outras']);
    }

    public function getOriginName(): string
    {
        return match($this->origin) {
            'meta' => 'Meta Ads',
            'google' => 'Google Ads',
            'outras' => 'Outras Origens',
            'nao_rastreada' => 'Não Rastreada',
            default => $this->origin,
        };
    }

    public function getStatusName(): string
    {
        return match($this->status) {
            'new' => 'Novo',
            'contacted' => 'Contatado',
            'qualified' => 'Qualificado',
            'converted' => 'Convertido',
            'lost' => 'Perdido',
            default => $this->status,
        };
    }

    public function updateLastMessage(): void
    {
        $this->update(['last_message_at' => now()]);
    }

    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
    }

    public function removeTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        $tags = array_filter($tags, fn($t) => $t !== $tag);
        $this->update(['tags' => array_values($tags)]);
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    public function getTotalConversions()
    {
        return $this->conversions()
            ->where('status', 'confirmed')
            ->sum('value');
    }

    public function getConversionsCount(): int
    {
        return $this->conversions()
            ->where('status', 'confirmed')
            ->count();
    }

    public function getLastConversation()
    {
        return $this->conversations()
            ->latest('last_activity_at')
            ->first();
    }

    public function markAsConverted(): void
    {
        $this->update(['status' => 'converted']);
    }

    public function createFromTracking(array $trackingData): self
    {
        $attribution = $this->determineAttribution($trackingData);
        
        return $this->create(array_merge($trackingData, $attribution));
    }

    private function determineAttribution(array $data): array
    {
        $origin = 'nao_rastreada';
        
        // Verifica UTM Source
        if (!empty($data['utm_source'])) {
            $source = strtolower($data['utm_source']);
            
            if (in_array($source, ['facebook', 'instagram', 'meta'])) {
                $origin = 'meta';
            } elseif (in_array($source, ['google', 'googleads'])) {
                $origin = 'google';
            } else {
                $origin = 'outras';
            }
        }
        
        // Verifica parâmetros específicos (fbclid, gclid)
        if (!empty($data['fbclid']) || !empty($data['attribution_data']['fbclid'])) {
            $origin = 'meta';
        } elseif (!empty($data['gclid']) || !empty($data['attribution_data']['gclid'])) {
            $origin = 'google';
        }
        
        // Verifica referrer
        if ($origin === 'nao_rastreada' && !empty($data['referrer_url'])) {
            $referrer = strtolower($data['referrer_url']);
            
            if (str_contains($referrer, 'facebook.com') || str_contains($referrer, 'instagram.com')) {
                $origin = 'meta';
            } elseif (str_contains($referrer, 'google.com')) {
                $origin = 'google';
            }
        }

        return ['origin' => $origin];
    }
}
