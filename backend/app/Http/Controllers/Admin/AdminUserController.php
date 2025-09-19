<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Listar todos os usuários
     */
    public function index(Request $request)
    {
        $query = User::with('company');

        // Filtros
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('company', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->role) {
            $query->where('role', $request->role);
        }

        if ($request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->created_from) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->created_to) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($users);
    }

    /**
     * Exibir usuário específico
     */
    public function show(Request $request, User $user)
    {
        $user->load(['company', 'assignedConversations', 'confirmedConversions']);

        return response()->json($user);
    }

    /**
     * Criar novo usuário (apenas super admin)
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:company_admin,company_agent,company_viewer,super_admin',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'company_id' => $request->company_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Log da criação
        AuditLog::create([
            'company_id' => $user->company_id,
            'user_id' => $request->user()->id,
            'event' => 'admin.user_created',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'new_values' => $user->toArray(),
            'metadata' => [
                'created_by_admin' => true,
                'target_company_id' => $user->company_id,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Usuário criado com sucesso',
            'user' => $user->load('company'),
        ], 201);
    }

    /**
     * Atualizar usuário
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:company_admin,company_agent,company_viewer,super_admin',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8',
        ]);

        $oldValues = $user->toArray();

        $data = $request->only(['name', 'email', 'role', 'phone', 'is_active']);
        
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Log da alteração
        AuditLog::create([
            'company_id' => $user->company_id,
            'user_id' => $request->user()->id,
            'event' => 'admin.user_updated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => $oldValues,
            'new_values' => $user->getChanges(),
            'metadata' => [
                'password_changed' => !empty($request->password),
                'target_company_id' => $user->company_id,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Usuário atualizado com sucesso',
            'user' => $user->fresh('company'),
        ]);
    }

    /**
     * Remover usuário
     */
    public function destroy(Request $request, User $user)
    {
        // Não permitir deletar super admin (exceto por outro super admin)
        if ($user->role === 'super_admin' && !$request->user()->isSuperAdmin()) {
            return response()->json([
                'error' => 'Não é possível remover um super administrador',
            ], 403);
        }

        // Não permitir deletar o próprio usuário
        if ($user->id === $request->user()->id) {
            return response()->json([
                'error' => 'Não é possível remover seu próprio usuário',
            ], 422);
        }

        // Verificar se é o último admin da empresa
        if ($user->role === 'company_admin') {
            $adminCount = User::where('company_id', $user->company_id)
                ->where('role', 'company_admin')
                ->where('is_active', true)
                ->count();

            if ($adminCount <= 1) {
                return response()->json([
                    'error' => 'Não é possível remover o último administrador da empresa',
                ], 422);
            }
        }

        // Log da remoção
        AuditLog::create([
            'company_id' => $user->company_id,
            'user_id' => $request->user()->id,
            'event' => 'admin.user_deleted',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => $user->toArray(),
            'metadata' => [
                'target_company_id' => $user->company_id,
                'target_user_role' => $user->role,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $user->delete();

        return response()->json([
            'message' => 'Usuário removido com sucesso',
        ]);
    }

    /**
     * Impersonar usuário (fazer login como outro usuário)
     */
    public function impersonate(Request $request, User $user)
    {
        // Não permitir impersonar super admin
        if ($user->role === 'super_admin') {
            return response()->json([
                'error' => 'Não é possível impersonar um super administrador',
            ], 403);
        }

        // Verificar se o usuário está ativo
        if (!$user->is_active) {
            return response()->json([
                'error' => 'Não é possível impersonar um usuário inativo',
            ], 422);
        }

        // Verificar se a empresa está ativa
        if (!$user->company || !$user->company->is_active) {
            return response()->json([
                'error' => 'Não é possível impersonar usuário de empresa inativa',
            ], 422);
        }

        // Criar token de impersonificação
        $token = $user->createToken('impersonation-token', ['*'], now()->addHours(2))->plainTextToken;

        // Log da impersonificação
        AuditLog::create([
            'company_id' => $user->company_id,
            'user_id' => $request->user()->id,
            'event' => 'admin.user_impersonated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'metadata' => [
                'impersonated_user_id' => $user->id,
                'impersonated_user_email' => $user->email,
                'original_admin_id' => $request->user()->id,
                'token_expires_at' => now()->addHours(2)->toISOString(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Impersonificação iniciada com sucesso',
            'user' => $user->load('company'),
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 2 * 60 * 60, // 2 horas
            'impersonation' => true,
            'original_admin' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    }

    /**
     * Ativar/desativar usuário
     */
    public function toggleStatus(Request $request, User $user)
    {
        // Não permitir desativar super admin
        if ($user->role === 'super_admin' && !$user->is_active) {
            return response()->json([
                'error' => 'Não é possível desativar um super administrador',
            ], 403);
        }

        // Não permitir desativar o próprio usuário
        if ($user->id === $request->user()->id) {
            return response()->json([
                'error' => 'Não é possível alterar o status do seu próprio usuário',
            ], 422);
        }

        $oldStatus = $user->is_active;
        $user->update(['is_active' => !$user->is_active]);

        $event = $user->is_active ? 'admin.user_activated' : 'admin.user_deactivated';

        // Log da mudança de status
        AuditLog::create([
            'company_id' => $user->company_id,
            'user_id' => $request->user()->id,
            'event' => $event,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => ['is_active' => $oldStatus],
            'new_values' => ['is_active' => $user->is_active],
            'metadata' => [
                'target_company_id' => $user->company_id,
                'target_user_role' => $user->role,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $status = $user->is_active ? 'ativado' : 'desativado';
        
        return response()->json([
            'message' => "Usuário {$status} com sucesso",
            'user' => $user->fresh('company'),
        ]);
    }

    /**
     * Redefinir senha do usuário
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Log da redefinição de senha
        AuditLog::create([
            'company_id' => $user->company_id,
            'user_id' => $request->user()->id,
            'event' => 'admin.user_password_reset',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'metadata' => [
                'target_user_email' => $user->email,
                'target_company_id' => $user->company_id,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Senha redefinida com sucesso',
        ]);
    }
}
