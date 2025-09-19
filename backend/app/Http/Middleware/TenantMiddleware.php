<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Super admin pode acessar qualquer tenant
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Verifica se a empresa do usuário está ativa
        if (!$user->company || !$user->company->is_active) {
            AuditLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'event' => 'access_denied_inactive_company',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'error' => 'Conta da empresa inativa',
                'message' => 'Entre em contato com o suporte para reativar sua conta.'
            ], 403);
        }

        // Verifica se o trial expirou
        if ($user->company->isTrialExpired()) {
            // Permite acesso apenas a endpoints específicos durante trial expirado
            $allowedPaths = [
                'api/user/profile',
                'api/auth/logout', 
                'api/trial/status',
                'api/trial/support-contact',
            ];

            $currentPath = $request->path();
            $isAllowed = false;

            foreach ($allowedPaths as $allowedPath) {
                if (str_starts_with($currentPath, $allowedPath)) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                return response()->json([
                    'error' => 'trial_expired',
                    'message' => 'Seu período de teste expirou. Entre em contato para renovar.',
                    'trial_expired_at' => $user->company->trial_expires_at,
                    'support_whatsapp_url' => $user->company->getWhatsAppSupportUrl(),
                ], 402); // 402 Payment Required
            }
        }

        // Adiciona company_id no contexto da request para facilitar queries
        $request->merge(['tenant_company_id' => $user->company_id]);
        
        // Define um escopo global para queries automáticas de tenant
        if (config('app.env') !== 'testing') {
            $this->applyTenantScope($user->company_id);
        }

        return $next($request);
    }

    /**
     * Aplica escopo de tenant para models que suportam
     */
    private function applyTenantScope(int $companyId): void
    {
        // Nota: Este é um exemplo simplificado. Em produção, você pode usar
        // packages como spatie/laravel-multitenancy para automação completa
        
        // Por enquanto, deixamos a responsabilidade para cada controller/query
        // verificar o company_id manualmente
    }
}
