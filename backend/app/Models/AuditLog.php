<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Relacionamentos
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    public function scopeByModel($query, $modelType, $modelId = null)
    {
        $query = $query->where('auditable_type', $modelType);
        
        if ($modelId) {
            $query = $query->where('auditable_id', $modelId);
        }
        
        return $query;
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeSecurityEvents($query)
    {
        return $query->whereIn('event', [
            'login',
            'logout',
            'login_failed',
            'password_changed',
            'two_factor_enabled',
            'two_factor_disabled',
            'user_created',
            'user_deleted',
            'role_changed',
            'trial_extended',
            'company_settings_changed',
        ]);
    }

    /**
     * Métodos auxiliares
     */
    public function getEventName(): string
    {
        return match($this->event) {
            'login' => 'Login realizado',
            'logout' => 'Logout realizado',
            'login_failed' => 'Tentativa de login falhada',
            'password_changed' => 'Senha alterada',
            'two_factor_enabled' => '2FA habilitado',
            'two_factor_disabled' => '2FA desabilitado',
            'user_created' => 'Usuário criado',
            'user_updated' => 'Usuário atualizado',
            'user_deleted' => 'Usuário excluído',
            'role_changed' => 'Função alterada',
            'company_created' => 'Empresa criada',
            'company_updated' => 'Empresa atualizada',
            'trial_extended' => 'Trial estendido',
            'lead_created' => 'Lead criado',
            'conversation_started' => 'Conversa iniciada',
            'conversion_created' => 'Conversão detectada',
            'conversion_confirmed' => 'Conversão confirmada',
            'webhook_created' => 'Webhook criado',
            'webhook_updated' => 'Webhook atualizado',
            'tracking_link_created' => 'Link rastreável criado',
            'tracking_link_updated' => 'Link rastreável atualizado',
            default => $this->event,
        };
    }

    public function getEventColor(): string
    {
        return match($this->event) {
            'login', 'user_created', 'company_created', 'conversion_confirmed' => 'green',
            'logout' => 'blue',
            'login_failed', 'user_deleted', 'trial_expired' => 'red',
            'password_changed', 'two_factor_enabled', 'role_changed', 'trial_extended' => 'orange',
            default => 'gray',
        };
    }

    public function isSecurityEvent(): bool
    {
        return in_array($this->event, [
            'login',
            'logout', 
            'login_failed',
            'password_changed',
            'two_factor_enabled',
            'two_factor_disabled',
            'user_created',
            'user_deleted',
            'role_changed',
            'trial_extended',
            'company_settings_changed',
        ]);
    }

    public function getModelName(): ?string
    {
        if (!$this->auditable_type) {
            return null;
        }

        return class_basename($this->auditable_type);
    }

    public function getChangedFields(): array
    {
        if (empty($this->old_values) || empty($this->new_values)) {
            return [];
        }

        $changed = [];
        
        foreach ($this->new_values as $field => $newValue) {
            $oldValue = $this->old_values[$field] ?? null;
            
            if ($oldValue !== $newValue) {
                $changed[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changed;
    }

    public function getFormattedChanges(): array
    {
        $changes = $this->getChangedFields();
        $formatted = [];

        foreach ($changes as $field => $change) {
            $formatted[] = [
                'field' => $this->getFieldLabel($field),
                'old' => $this->formatValue($change['old']),
                'new' => $this->formatValue($change['new']),
            ];
        }

        return $formatted;
    }

    private function getFieldLabel(string $field): string
    {
        return match($field) {
            'name' => 'Nome',
            'email' => 'E-mail',
            'phone' => 'Telefone',
            'role' => 'Função',
            'is_active' => 'Ativo',
            'trial_expires_at' => 'Trial expira em',
            'status' => 'Status',
            'value' => 'Valor',
            'payment_method' => 'Método de pagamento',
            default => ucfirst(str_replace('_', ' ', $field)),
        };
    }

    private function formatValue($value): string
    {
        if ($value === null) {
            return 'N/A';
        }

        if (is_bool($value)) {
            return $value ? 'Sim' : 'Não';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }

        return (string) $value;
    }

    /**
     * Métodos estáticos para criar logs
     */
    public static function logLogin(User $user, string $ipAddress = null): void
    {
        self::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'event' => 'login',
            'ip_address' => $ipAddress ?: request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function logLogout(User $user): void
    {
        self::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'event' => 'logout',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function logFailedLogin(string $email, string $ipAddress = null): void
    {
        self::create([
            'event' => 'login_failed',
            'ip_address' => $ipAddress ?: request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => ['email' => $email],
        ]);
    }

    public static function logTrialExtension(Company $company, User $adminUser, int $days): void
    {
        self::create([
            'company_id' => $company->id,
            'user_id' => $adminUser->id,
            'event' => 'trial_extended',
            'auditable_type' => Company::class,
            'auditable_id' => $company->id,
            'metadata' => ['days_extended' => $days],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Limpeza automática de logs antigos
     */
    public static function cleanup(int $daysToKeep = 365): int
    {
        return self::where('created_at', '<', now()->subDays($daysToKeep))->delete();
    }
}
