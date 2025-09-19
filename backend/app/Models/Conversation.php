<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'company_id',
        'whatsapp_conversation_id',
        'started_at',
        'last_activity_at',
        'status',
        'assigned_to',
        'message_count',
        'has_unread',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'has_unread' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Relacionamentos
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
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

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeWithUnread($query)
    {
        return $query->where('has_unread', true);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('last_activity_at', '>=', now()->subDays($days));
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['open', 'qualified']);
    }

    /**
     * Métodos auxiliares
     */
    public function getStatusName(): string
    {
        return match($this->status) {
            'open' => 'Aberta',
            'closed' => 'Fechada',
            'qualified' => 'Qualificada',
            'converted' => 'Convertida',
            'lost' => 'Perdida',
            default => $this->status,
        };
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['open', 'qualified']);
    }

    public function assignTo(User $user): void
    {
        $this->update([
            'assigned_to' => $user->id,
            'has_unread' => false,
        ]);
    }

    public function unassign(): void
    {
        $this->update(['assigned_to' => null]);
    }

    public function markAsRead(): void
    {
        $this->update(['has_unread' => false]);
    }

    public function markAsUnread(): void
    {
        $this->update(['has_unread' => true]);
    }

    public function updateActivity(): void
    {
        $this->update([
            'last_activity_at' => now(),
            'has_unread' => true,
        ]);
    }

    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'has_unread' => false,
        ]);
    }

    public function reopen(): void
    {
        $this->update([
            'status' => 'open',
            'has_unread' => true,
        ]);
    }

    public function qualify(): void
    {
        $this->update(['status' => 'qualified']);
        $this->lead->update(['status' => 'qualified']);
    }

    public function markAsConverted(): void
    {
        $this->update(['status' => 'converted']);
        $this->lead->markAsConverted();
    }

    public function markAsLost(): void
    {
        $this->update(['status' => 'lost']);
        $this->lead->update(['status' => 'lost']);
    }

    public function getLastMessage()
    {
        return $this->messages()->latest()->first();
    }

    public function getTotalConversions()
    {
        return $this->conversions()
            ->where('status', 'confirmed')
            ->sum('value');
    }

    public function hasConversions(): bool
    {
        return $this->conversions()
            ->where('status', 'confirmed')
            ->exists();
    }

    public function getDurationInMinutes(): ?int
    {
        if (!$this->started_at || !$this->last_activity_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->last_activity_at);
    }

    public function getResponseTime(): ?int
    {
        $firstClientMessage = $this->messages()
            ->where('sender', 'client')
            ->orderBy('created_at')
            ->first();

        $firstAgentMessage = $this->messages()
            ->where('sender', 'agent')
            ->orderBy('created_at')
            ->first();

        if (!$firstClientMessage || !$firstAgentMessage) {
            return null;
        }

        return $firstClientMessage->created_at->diffInMinutes($firstAgentMessage->created_at);
    }

    public function shouldFollowUp(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $lastMessage = $this->getLastMessage();
        if (!$lastMessage) {
            return false;
        }

        // Se a última mensagem foi do cliente e já passou tempo suficiente
        if ($lastMessage->sender === 'client') {
            $followUpHours = config('whatsapp.automation.follow_up_delay_hours', 24);
            return $lastMessage->created_at->addHours($followUpHours)->isPast();
        }

        return false;
    }
}
