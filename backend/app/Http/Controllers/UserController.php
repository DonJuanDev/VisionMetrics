<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Get user profile
     */
    public function profile(): JsonResponse
    {
        $user = auth()->user();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'company' => [
                'id' => $user->company->id,
                'name' => $user->company->name,
            ],
            'two_factor_enabled' => !empty($user->two_factor_secret),
        ]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'sometimes|nullable|string|max:50',
        ]);
        
        $user->update($validated);
        
        return response()->json([
            'message' => 'Perfil atualizado com sucesso!',
            'user' => $user,
        ]);
    }
    
    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = auth()->user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Senha atual incorreta.',
            ], 400);
        }
        
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        
        return response()->json([
            'message' => 'Senha alterada com sucesso!',
        ]);
    }
    
    /**
     * List users (company admin only)
     */
    public function index(): JsonResponse
    {
        $users = auth()->user()->company->users()->get();
        
        return response()->json([
            'users' => $users,
        ]);
    }
    
    /**
     * Store new user (company admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:company_agent,company_viewer',
        ]);
        
        $user = User::create([
            'company_id' => auth()->user()->company_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
        ]);
        
        return response()->json([
            'message' => 'Usuário criado com sucesso!',
            'user' => $user,
        ], 201);
    }
    
    /**
     * Update user (company admin only)
     */
    public function update(Request $request, User $user): JsonResponse
    {
        // Verificar se o usuário pertence à mesma empresa
        if ($user->company_id !== auth()->user()->company_id) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'sometimes|nullable|string|max:50',
            'role' => 'sometimes|in:company_agent,company_viewer',
        ]);
        
        $user->update($validated);
        
        return response()->json([
            'message' => 'Usuário atualizado com sucesso!',
            'user' => $user,
        ]);
    }
    
    /**
     * Delete user (company admin only)
     */
    public function destroy(User $user): JsonResponse
    {
        // Verificar se o usuário pertence à mesma empresa
        if ($user->company_id !== auth()->user()->company_id) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }
        
        // Não permitir deletar o próprio usuário
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Você não pode deletar seu próprio usuário.'], 400);
        }
        
        $user->delete();
        
        return response()->json([
            'message' => 'Usuário removido com sucesso!',
        ]);
    }
    
    /**
     * Toggle user status (company admin only)
     */
    public function toggleStatus(User $user): JsonResponse
    {
        // Verificar se o usuário pertence à mesma empresa
        if ($user->company_id !== auth()->user()->company_id) {
            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }
        
        // Implementar lógica de ativar/desativar quando necessário
        return response()->json([
            'message' => 'Status alterado com sucesso!',
            'user' => $user,
        ]);
    }
    
    /**
     * Enable two factor authentication
     */
    public function enableTwoFactor(Request $request): JsonResponse
    {
        // Placeholder for 2FA implementation
        return response()->json([
            'message' => '2FA será implementado em versão futura.',
        ]);
    }
    
    /**
     * Disable two factor authentication
     */
    public function disableTwoFactor(Request $request): JsonResponse
    {
        // Placeholder for 2FA implementation
        return response()->json([
            'message' => '2FA será implementado em versão futura.',
        ]);
    }
}
