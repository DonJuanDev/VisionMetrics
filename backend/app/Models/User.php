<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'two_factor_recovery_codes' => 'array',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relacionamentos
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'assigned_to');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sent_by');
    }

    public function confirmedConversions(): HasMany
    {
        return $this->hasMany(Conversion::class, 'confirmed_by');
    }

    public function createdTrackingLinks(): HasMany
    {
        return $this->hasMany(TrackingLink::class, 'created_by');
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

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['super_admin', 'company_admin']);
    }

    /**
     * Métodos de autorização
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isCompanyAdmin(): bool
    {
        return $this->role === 'company_admin';
    }

    public function isAgent(): bool
    {
        return $this->role === 'company_agent';
    }

    public function isViewer(): bool
    {
        return $this->role === 'company_viewer';
    }

    public function canManageUsers(): bool
    {
        return in_array($this->role, ['super_admin', 'company_admin']);
    }

    public function canViewReports(): bool
    {
        return in_array($this->role, ['super_admin', 'company_admin', 'company_agent', 'company_viewer']);
    }

    public function canManageConversations(): bool
    {
        return in_array($this->role, ['super_admin', 'company_admin', 'company_agent']);
    }

    public function canManageCompanySettings(): bool
    {
        return in_array($this->role, ['super_admin', 'company_admin']);
    }

    public function hasPermission(string $permission): bool
    {
        $rolePermissions = config('auth.roles.' . $this->role . '.permissions', []);
        
        // Super admin tem todas as permissões
        if (in_array('*', $rolePermissions)) {
            return true;
        }

        // Verifica permissão específica ou wildcard
        foreach ($rolePermissions as $rolePermission) {
            if ($rolePermission === $permission) {
                return true;
            }
            
            // Verifica wildcard (ex: company.* para company.create)
            if (str_ends_with($rolePermission, '*')) {
                $prefix = substr($rolePermission, 0, -1);
                if (str_starts_with($permission, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Métodos auxiliares
     */
    public function getRoleName(): string
    {
        return config('auth.roles.' . $this->role . '.name', $this->role);
    }

    public function belongsToCompany(int $companyId): bool
    {
        return $this->company_id === $companyId;
    }

    public function updateLastLogin(string $ipAddress = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress ?: request()->ip(),
        ]);
    }

    /**
     * Two-Factor Authentication
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret);
    }

    public function enableTwoFactor(string $secret): void
    {
        $this->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function disableTwoFactor(): void
    {
        $this->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ]);
    }

    public function getTwoFactorSecret(): ?string
    {
        return $this->two_factor_secret ? decrypt($this->two_factor_secret) : null;
    }
}
