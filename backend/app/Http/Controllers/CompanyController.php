<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    /**
     * Get trial status for current company
     */
    public function trialStatus(): JsonResponse
    {
        $company = auth()->user()->company;
        
        $isTrialActive = $company->trial_expires_at && now()->lt($company->trial_expires_at);
        $daysRemaining = $company->trial_expires_at ? now()->diffInDays($company->trial_expires_at, false) : 0;
        
        return response()->json([
            'is_active' => $isTrialActive,
            'expires_at' => $company->trial_expires_at,
            'days_remaining' => max(0, $daysRemaining),
            'support_contact' => config('app.whatsapp_support_number'),
        ]);
    }
    
    /**
     * Get support contact information
     */
    public function supportContact(): JsonResponse
    {
        return response()->json([
            'whatsapp' => config('app.whatsapp_support_number'),
            'email' => config('mail.from.address'),
            'message_template' => 'Olá! Preciso de ajuda com meu trial do VisionMetrics.',
        ]);
    }
    
    /**
     * Get company settings
     */
    public function settings(): JsonResponse
    {
        $company = auth()->user()->company;
        
        return response()->json([
            'id' => $company->id,
            'name' => $company->name,
            'email' => $company->email,
            'phone' => $company->phone,
            'cnpj' => $company->cnpj,
            'timezone' => $company->timezone,
            'trial_expires_at' => $company->trial_expires_at,
        ]);
    }
    
    /**
     * Update company settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $company = auth()->user()->company;
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|string|max:50',
            'cnpj' => 'sometimes|nullable|string|max:20',
            'timezone' => 'sometimes|string|max:50',
        ]);
        
        $company->update($validated);
        
        return response()->json([
            'message' => 'Configurações atualizadas com sucesso!',
            'company' => $company,
        ]);
    }
    
    /**
     * Get integrations
     */
    public function integrations(): JsonResponse
    {
        return response()->json([
            'whatsapp' => [
                'configured' => !empty(config('whatsapp.cloud_api_token')),
                'webhook_url' => url('/api/webhooks/whatsapp'),
            ],
            'meta_ads' => [
                'configured' => !empty(config('services.meta.access_token')),
            ],
            'google_ads' => [
                'configured' => !empty(config('services.google.ads.client_id')),
            ],
        ]);
    }
    
    /**
     * Update integrations
     */
    public function updateIntegrations(Request $request): JsonResponse
    {
        // Placeholder - as integrações são configuradas via .env
        return response()->json([
            'message' => 'Configurações de integração são gerenciadas via arquivo de configuração.',
            'note' => 'Entre em contato com o suporte para configurar integrações.',
        ]);
    }
}
