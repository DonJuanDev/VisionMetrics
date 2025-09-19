<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrialMiddleware
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

        // Super admin bypass
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Verifica se a empresa existe
        if (!$user->company) {
            return response()->json(['error' => 'Empresa não encontrada'], 404);
        }

        // Verifica se o trial expirou
        if ($user->company->isTrialExpired()) {
            return response()->json([
                'error' => 'trial_expired',
                'message' => 'Seu período de teste de ' . config('app.trial_days', 7) . ' dias expirou.',
                'expired_at' => $user->company->trial_expires_at,
                'support_contact' => [
                    'whatsapp_url' => $user->company->getWhatsAppSupportUrl(),
                    'message' => 'Entre em contato conosco pelo WhatsApp para renovar sua assinatura.',
                ],
                'trial_info' => [
                    'started_at' => $user->company->created_at,
                    'expired_at' => $user->company->trial_expires_at,
                    'days_used' => $user->company->created_at->diffInDays($user->company->trial_expires_at),
                ]
            ], 402); // 402 Payment Required
        }

        return $next($request);
    }
}
