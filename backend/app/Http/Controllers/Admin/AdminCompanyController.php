<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\User;
use App\Models\Lead;
use App\Models\Conversation;
use App\Models\Conversion;
use App\Models\AuditLog;
use Carbon\Carbon;

class AdminCompanyController extends Controller
{
    /**
     * Listar todas as empresas
     */
    public function index(Request $request)
    {
        $query = Company::with(['users' => function($q) {
            $q->where('role', 'company_admin')->limit(1);
        }])->withCount(['users', 'leads', 'conversations']);

        // Filtros
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%");
            });
        }

        if ($request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'trial_expired') {
                $query->where('trial_expires_at', '<', now());
            } elseif ($request->status === 'trial_active') {
                $query->where('trial_expires_at', '>', now());
            }
        }

        if ($request->created_from) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->created_to) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        $companies = $query->orderBy('created_at', 'desc')->paginate(20);

        // Adicionar informações calculadas
        $companies->getCollection()->transform(function ($company) {
            $company->is_trial_expired = $company->isTrialExpired();
            $company->remaining_trial_days = $company->getRemainingTrialDays();
            $company->admin_user = $company->users->first();
            return $company;
        });

        return response()->json($companies);
    }

    /**
     * Exibir empresa específica
     */
    public function show(Request $request, Company $company)
    {
        $company->load([
            'users',
            'leads' => function($q) { $q->limit(10)->orderBy('created_at', 'desc'); },
            'conversations' => function($q) { $q->limit(10)->orderBy('created_at', 'desc'); },
            'conversions' => function($q) { $q->limit(10)->orderBy('created_at', 'desc'); }
        ]);

        $company->is_trial_expired = $company->isTrialExpired();
        $company->remaining_trial_days = $company->getRemainingTrialDays();
        $company->stats = $this->getCompanyStats($company->id);

        return response()->json($company);
    }

    /**
     * Atualizar empresa
     */
    public function update(Request $request, Company $company)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'cnpj' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $oldValues = $company->toArray();
        
        $company->update($request->only([
            'name', 'email', 'phone', 'cnpj', 'timezone', 'is_active'
        ]));

        // Log da alteração
        AuditLog::create([
            'company_id' => $company->id,
            'user_id' => $request->user()->id,
            'event' => 'admin.company_updated',
            'auditable_type' => Company::class,
            'auditable_id' => $company->id,
            'old_values' => $oldValues,
            'new_values' => $company->getChanges(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Empresa atualizada com sucesso',
            'company' => $company,
        ]);
    }

    /**
     * Estender trial da empresa
     */
    public function extendTrial(Request $request, Company $company)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'reason' => 'nullable|string|max:500',
        ]);

        $oldExpiresAt = $company->trial_expires_at;
        $company->extendTrial($request->days);

        // Log da extensão
        AuditLog::create([
            'company_id' => $company->id,
            'user_id' => $request->user()->id,
            'event' => 'admin.trial_extended',
            'auditable_type' => Company::class,
            'auditable_id' => $company->id,
            'old_values' => ['trial_expires_at' => $oldExpiresAt],
            'new_values' => ['trial_expires_at' => $company->trial_expires_at],
            'metadata' => [
                'days_extended' => $request->days,
                'reason' => $request->reason,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => "Trial estendido por {$request->days} dias com sucesso",
            'company' => $company->fresh(),
        ]);
    }

    /**
     * Ativar/desativar empresa
     */
    public function toggleStatus(Request $request, Company $company)
    {
        $oldStatus = $company->is_active;
        $company->update(['is_active' => !$company->is_active]);

        $event = $company->is_active ? 'admin.company_activated' : 'admin.company_deactivated';

        // Log da mudança de status
        AuditLog::create([
            'company_id' => $company->id,
            'user_id' => $request->user()->id,
            'event' => $event,
            'auditable_type' => Company::class,
            'auditable_id' => $company->id,
            'old_values' => ['is_active' => $oldStatus],
            'new_values' => ['is_active' => $company->is_active],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $status = $company->is_active ? 'ativada' : 'desativada';
        
        return response()->json([
            'message' => "Empresa {$status} com sucesso",
            'company' => $company,
        ]);
    }

    /**
     * Logs de auditoria da empresa
     */
    public function auditLogs(Request $request, Company $company)
    {
        $query = AuditLog::with('user')
            ->where('company_id', $company->id)
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->event) {
            $query->where('event', $request->event);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);

        return response()->json($logs);
    }

    /**
     * Estatísticas da empresa
     */
    public function companyStats(Request $request, Company $company)
    {
        return response()->json($this->getCompanyStats($company->id));
    }

    /**
     * Remover empresa (soft delete)
     */
    public function destroy(Request $request, Company $company)
    {
        // Verificar se tem dados importantes
        $hasConversions = $company->conversions()->where('status', 'confirmed')->exists();
        
        if ($hasConversions && !$request->force) {
            return response()->json([
                'error' => 'company_has_conversions',
                'message' => 'Esta empresa possui conversões confirmadas. Use force=true para confirmar a remoção.',
            ], 422);
        }

        // Log da remoção
        AuditLog::create([
            'company_id' => $company->id,
            'user_id' => $request->user()->id,
            'event' => 'admin.company_deleted',
            'auditable_type' => Company::class,
            'auditable_id' => $company->id,
            'old_values' => $company->toArray(),
            'metadata' => [
                'force_delete' => $request->boolean('force'),
                'reason' => $request->reason,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $company->delete();

        return response()->json([
            'message' => 'Empresa removida com sucesso',
        ]);
    }

    /**
     * Obter estatísticas da empresa
     */
    private function getCompanyStats(int $companyId): array
    {
        $now = now();
        $thisMonth = $now->copy()->startOfMonth();

        // Stats básicas
        $totalUsers = User::where('company_id', $companyId)->count();
        $activeUsers = User::where('company_id', $companyId)->where('is_active', true)->count();
        
        $totalLeads = Lead::where('company_id', $companyId)->count();
        $leadsThisMonth = Lead::where('company_id', $companyId)
            ->where('created_at', '>=', $thisMonth)->count();
        
        $totalConversations = Conversation::where('company_id', $companyId)->count();
        $activeConversations = Conversation::where('company_id', $companyId)
            ->whereIn('status', ['open', 'qualified'])->count();
        
        $totalConversions = Conversion::where('company_id', $companyId)
            ->where('status', 'confirmed')->count();
        $totalRevenue = Conversion::where('company_id', $companyId)
            ->where('status', 'confirmed')->sum('value');
        $conversionsThisMonth = Conversion::where('company_id', $companyId)
            ->where('status', 'confirmed')
            ->where('confirmed_at', '>=', $thisMonth)->count();
        $revenueThisMonth = Conversion::where('company_id', $companyId)
            ->where('status', 'confirmed')
            ->where('confirmed_at', '>=', $thisMonth)->sum('value');

        // Leads por origem
        $leadsByOrigin = Lead::where('company_id', $companyId)
            ->selectRaw('origin, COUNT(*) as count')
            ->groupBy('origin')
            ->pluck('count', 'origin')
            ->toArray();

        return [
            'users' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
            ],
            'leads' => [
                'total' => $totalLeads,
                'this_month' => $leadsThisMonth,
                'by_origin' => $leadsByOrigin,
            ],
            'conversations' => [
                'total' => $totalConversations,
                'active' => $activeConversations,
            ],
            'conversions' => [
                'total' => $totalConversions,
                'this_month' => $conversionsThisMonth,
            ],
            'revenue' => [
                'total' => $totalRevenue,
                'this_month' => $revenueThisMonth,
                'formatted_total' => 'R$ ' . number_format($totalRevenue, 2, ',', '.'),
                'formatted_this_month' => 'R$ ' . number_format($revenueThisMonth, 2, ',', '.'),
            ],
        ];
    }
}
