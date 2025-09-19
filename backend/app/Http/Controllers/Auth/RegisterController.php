<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Company;
use App\Models\User;
use App\Models\AuditLog;
use Carbon\Carbon;

class RegisterController extends Controller
{
    /**
     * Registrar nova empresa
     */
    public function registerCompany(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:50',
            'password' => 'required|string|min:8|confirmed',
            'cnpj' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
            'terms_accepted' => 'required|accepted',
        ], [
            'company_name.required' => 'O nome da empresa é obrigatório.',
            'admin_name.required' => 'O nome do administrador é obrigatório.',
            'admin_email.required' => 'O email é obrigatório.',
            'admin_email.email' => 'O email deve ser um endereço válido.',
            'admin_email.unique' => 'Este email já está em uso.',
            'phone.required' => 'O telefone é obrigatório.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'terms_accepted.accepted' => 'Você deve aceitar os termos de uso.',
        ]);

        try {
            DB::beginTransaction();

            // Criar empresa
            $company = Company::create([
                'name' => $request->company_name,
                'email' => $request->admin_email,
                'phone' => $request->phone,
                'cnpj' => $request->cnpj,
                'timezone' => $request->timezone ?? 'America/Sao_Paulo',
                'trial_expires_at' => now()->addDays(config('app.trial_days', 7)),
                'is_active' => true,
            ]);

            // Criar usuário administrador
            $user = User::create([
                'company_id' => $company->id,
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->password),
                'role' => 'company_admin',
                'phone' => $request->phone,
                'is_active' => true,
            ]);

            // Cria token de acesso
            $token = $user->createToken('auth-token', ['*'], now()->addDays(30))->plainTextToken;

            // Log de criação
            AuditLog::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'event' => 'company_created',
                'auditable_type' => Company::class,
                'auditable_id' => $company->id,
                'new_values' => $company->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'registration_source' => 'web',
                    'trial_days' => config('app.trial_days', 7),
                ],
            ]);

            AuditLog::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'event' => 'user_created',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'new_values' => $user->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Empresa registrada com sucesso! Seu período de teste de ' . config('app.trial_days', 7) . ' dias começou agora.',
                'company' => $company,
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 30 * 24 * 60 * 60,
                'trial_info' => [
                    'expires_at' => $company->trial_expires_at,
                    'days_remaining' => $company->getRemainingTrialDays(),
                    'support_contact' => $company->getWhatsAppSupportUrl(),
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível registrar a empresa. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Verificar se email está disponível
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists 
                ? 'Este email já está em uso.' 
                : 'Email disponível.',
        ]);
    }

    /**
     * Verificar se CNPJ está disponível
     */
    public function checkCnpj(Request $request)
    {
        $request->validate([
            'cnpj' => 'required|string',
        ]);

        $exists = Company::where('cnpj', $request->cnpj)->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists 
                ? 'Este CNPJ já está cadastrado.' 
                : 'CNPJ disponível.',
        ]);
    }
}
