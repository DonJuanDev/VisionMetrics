<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'company_id',
        'whatsapp_message_id',
        'sender',
        'type',
        'body',
        'attachments',
        'status',
        'is_parsed',
        'parsed_value',
        'parsed_currency',
        'nlp_data',
        'sent_by',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_parsed' => 'boolean',
        'parsed_value' => 'decimal:2',
        'nlp_data' => 'array',
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

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Scopes
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeBySender($query, $sender)
    {
        return $query->where('sender', $sender);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeFromClient($query)
    {
        return $query->where('sender', 'client');
    }

    public function scopeFromAgent($query)
    {
        return $query->where('sender', 'agent');
    }

    public function scopeWithValue($query)
    {
        return $query->whereNotNull('parsed_value')->where('parsed_value', '>', 0);
    }

    public function scopeUnparsed($query)
    {
        return $query->where('is_parsed', false);
    }

    public function scopeRecent($query, $minutes = 60)
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Métodos auxiliares
     */
    public function getSenderName(): string
    {
        return match($this->sender) {
            'client' => 'Cliente',
            'agent' => 'Agente',
            'system' => 'Sistema',
            default => $this->sender,
        };
    }

    public function getTypeName(): string
    {
        return match($this->type) {
            'text' => 'Texto',
            'image' => 'Imagem',
            'document' => 'Documento',
            'audio' => 'Áudio',
            'video' => 'Vídeo',
            'location' => 'Localização',
            'contact' => 'Contato',
            default => $this->type,
        };
    }

    public function isFromClient(): bool
    {
        return $this->sender === 'client';
    }

    public function isFromAgent(): bool
    {
        return $this->sender === 'agent';
    }

    public function isFromSystem(): bool
    {
        return $this->sender === 'system';
    }

    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    public function getAttachment(string $type): ?array
    {
        return $this->attachments[$type] ?? null;
    }

    public function addAttachment(string $type, array $data): void
    {
        $attachments = $this->attachments ?? [];
        $attachments[$type] = $data;
        $this->update(['attachments' => $attachments]);
    }

    /**
     * NLP e parsing de valores
     */
    public function parseForValues(): void
    {
        if ($this->is_parsed || !$this->body) {
            return;
        }

        $patterns = config('whatsapp.conversion_patterns.value_patterns', []);
        $value = null;
        $currency = 'BRL';

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $this->body, $matches)) {
                $valueStr = $matches[1];
                // Converte formato brasileiro para float
                $value = (float) str_replace(['.', ','], ['', '.'], $valueStr);
                break;
            }
        }

        $this->update([
            'is_parsed' => true,
            'parsed_value' => $value,
            'parsed_currency' => $currency,
        ]);

        // Se encontrou valor, pode criar conversão automaticamente
        if ($value && $value > 0) {
            $this->createAutomaticConversion($value, $currency);
        }
    }

    public function detectConversionKeywords(): array
    {
        if (!$this->body) {
            return [];
        }

        $keywords = config('whatsapp.conversion_patterns.keywords', []);
        $paymentMethods = config('whatsapp.conversion_patterns.payment_methods', []);
        
        $detected = [
            'conversion_keywords' => [],
            'payment_method' => null,
            'confidence' => 0,
        ];

        $bodyLower = strtolower($this->body);

        // Verifica keywords de conversão
        foreach ($keywords as $keyword) {
            if (str_contains($bodyLower, strtolower($keyword))) {
                $detected['conversion_keywords'][] = $keyword;
                $detected['confidence'] += 0.2;
            }
        }

        // Verifica métodos de pagamento
        foreach ($paymentMethods as $method => $terms) {
            foreach ($terms as $term) {
                if (str_contains($bodyLower, strtolower($term))) {
                    $detected['payment_method'] = $method;
                    $detected['confidence'] += 0.3;
                    break 2;
                }
            }
        }

        return $detected;
    }

    private function createAutomaticConversion(float $value, string $currency): void
    {
        $nlpData = $this->detectConversionKeywords();
        
        // Só cria conversão automática se tiver alta confiança
        if ($nlpData['confidence'] < 0.5) {
            return;
        }

        Conversion::create([
            'conversation_id' => $this->conversation_id,
            'company_id' => $this->company_id,
            'lead_id' => $this->conversation->lead_id,
            'value' => $value,
            'currency' => $currency,
            'payment_method' => $nlpData['payment_method'],
            'detected_by' => 'nlp',
            'detection_data' => [
                'message_id' => $this->id,
                'nlp_data' => $nlpData,
                'confidence' => $nlpData['confidence'],
            ],
            'status' => 'pending', // Precisa confirmação
            'detected_at' => now(),
        ]);
    }

    /**
     * Boot method
     */
    protected static function booted()
    {
        static::created(function ($message) {
            // Atualiza contadores da conversa
            $message->conversation->increment('message_count');
            $message->conversation->updateActivity();
            
            // Atualiza lead
            $message->conversation->lead->updateLastMessage();
            
            // Parse automático se habilitado
            if (config('whatsapp.message_processing.auto_nlp', true)) {
                $message->parseForValues();
            }
        });
    }
}
