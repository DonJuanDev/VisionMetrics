<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\AuditLog;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Login do usuário
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Log tentativa de login falhada
            AuditLog::logFailedLogin($request->email, $request->ip());

            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas são inválidas.'],
            ]);
        }

        // Verifica se o usuário está ativo
        if (!$user->is_active) {
            return response()->json([
                'error' => 'Usuário inativo',
                'message' => 'Sua conta foi desativada. Entre em contato com o administrador.',
            ], 403);
        }

        // Verifica se a empresa está ativa
        if (!$user->company || !$user->company->is_active) {
            return response()->json([
                'error' => 'Empresa inativa',
                'message' => 'A conta da empresa foi desativada. Entre em contato com o suporte.',
            ], 403);
        }

        // Atualiza informações de login
        $user->updateLastLogin($request->ip());

        // Cria token de acesso
        $token = $user->createToken('auth-token', ['*'], now()->addDays(30))->plainTextToken;

        // Log login bem-sucedido
        AuditLog::logLogin($user, $request->ip());

        return response()->json([
            'user' => $user->load('company'),
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60, // 30 dias em segundos
            'company' => [
                'id' => $user->company->id,
                'name' => $user->company->name,
                'trial_expires_at' => $user->company->trial_expires_at,
                'is_trial_expired' => $user->company->isTrialExpired(),
                'remaining_trial_days' => $user->company->getRemainingTrialDays(),
            ],
        ]);
    }

    /**
     * Logout do usuário
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        // Log logout
        AuditLog::logLogout($user);

        // Revoga token atual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso',
        ]);
    }

    /**
     * Informações do usuário autenticado
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('company');

        return response()->json([
            'user' => $user,
            'company' => [
                'id' => $user->company->id,
                'name' => $user->company->name,
                'trial_expires_at' => $user->company->trial_expires_at,
                'is_trial_expired' => $user->company->isTrialExpired(),
                'remaining_trial_days' => $user->company->getRemainingTrialDays(),
                'whatsapp_support_url' => $user->company->getWhatsAppSupportUrl(),
            ],
            'permissions' => [
                'can_manage_users' => $user->canManageUsers(),
                'can_view_reports' => $user->canViewReports(),
                'can_manage_conversations' => $user->canManageConversations(),
                'can_manage_company_settings' => $user->canManageCompanySettings(),
                'is_super_admin' => $user->isSuperAdmin(),
            ],
        ]);
    }

    /**
     * Refresh token (gera novo token)
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        
        // Revoga token atual
        $request->user()->currentAccessToken()->delete();
        
        // Cria novo token
        $token = $user->createToken('auth-token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60,
        ]);
    }

    /**
     * Esqueci minha senha
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // TODO: Implementar envio de email de reset de senha
        // Por enquanto, apenas simula o processo

        return response()->json([
            'message' => 'Se o email existir em nosso sistema, você receberá um link para redefinir sua senha.',
        ]);
    }

    /**
     * Redefinir senha
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // TODO: Implementar validação de token de reset
        // Por enquanto, apenas simula o processo

        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Log alteração de senha
            AuditLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'event' => 'password_changed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json([
            'message' => 'Senha redefinida com sucesso.',
        ]);
    }
}
