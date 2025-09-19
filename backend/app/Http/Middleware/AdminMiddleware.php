<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $level = 'admin'): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $hasAccess = match($level) {
            'super_admin' => $user->isSuperAdmin(),
            'company_admin' => $user->isSuperAdmin() || $user->isCompanyAdmin(),
            'admin' => $user->canManageUsers(),
            default => false,
        };

        if (!$hasAccess) {
            // Log tentativa de acesso não autorizado
            AuditLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'event' => 'unauthorized_access_attempt',
                'metadata' => [
                    'required_level' => $level,
                    'user_role' => $user->role,
                    'requested_path' => $request->path(),
                    'request_method' => $request->method(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'error' => 'Acesso negado',
                'message' => 'Você não tem permissão para acessar este recurso.',
                'required_role' => $level,
                'your_role' => $user->role,
            ], 403);
        }

        return $next($request);
    }
}
