<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use App\Models\TrackingLink;
use App\Models\Lead;
use App\Models\AuditLog;

class TrackingController extends Controller
{
    /**
     * Listar links rastreáveis
     */
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $links = TrackingLink::forCompany($companyId)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($links);
    }

    /**
     * Criar novo link rastreável
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'destination_url' => 'required|url',
            'utm_source' => 'nullable|string|max:255',
            'utm_campaign' => 'nullable|string|max:255',
            'utm_medium' => 'nullable|string|max:255',
            'utm_term' => 'nullable|string|max:255',
            'utm_content' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $link = TrackingLink::createLink([
            'company_id' => $request->user()->company_id,
            'name' => $request->name,
            'destination_url' => $request->destination_url,
            'utm_source' => $request->utm_source,
            'utm_campaign' => $request->utm_campaign,
            'utm_medium' => $request->utm_medium,
            'utm_term' => $request->utm_term,
            'utm_content' => $request->utm_content,
            'expires_at' => $request->expires_at,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'link' => $link,
            'tracking_url' => $link->generateTrackingUrl(),
            'qr_code_url' => $link->getQrCodeUrl(),
        ], 201);
    }

    /**
     * Exibir link rastreável
     */
    public function show(Request $request, TrackingLink $trackingLink)
    {
        $this->authorize('view', $trackingLink);

        return response()->json([
            'link' => $trackingLink->load('creator'),
            'stats' => $trackingLink->getStats(),
            'tracking_url' => $trackingLink->generateTrackingUrl(),
            'qr_code_url' => $trackingLink->getQrCodeUrl(),
        ]);
    }

    /**
     * Atualizar link rastreável
     */
    public function update(Request $request, TrackingLink $trackingLink)
    {
        $this->authorize('update', $trackingLink);

        $request->validate([
            'name' => 'required|string|max:255',
            'destination_url' => 'required|url',
            'utm_source' => 'nullable|string|max:255',
            'utm_campaign' => 'nullable|string|max:255',
            'utm_medium' => 'nullable|string|max:255',
            'utm_term' => 'nullable|string|max:255',
            'utm_content' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $trackingLink->update($request->only([
            'name', 'destination_url', 'utm_source', 'utm_campaign',
            'utm_medium', 'utm_term', 'utm_content', 'expires_at'
        ]));

        return response()->json($trackingLink);
    }

    /**
     * Remover link rastreável
     */
    public function destroy(Request $request, TrackingLink $trackingLink)
    {
        $this->authorize('delete', $trackingLink);

        $trackingLink->delete();

        return response()->json(['message' => 'Link rastreável removido com sucesso']);
    }

    /**
     * Ativar/desativar link
     */
    public function toggle(Request $request, TrackingLink $trackingLink)
    {
        $this->authorize('update', $trackingLink);

        $trackingLink->update(['is_active' => !$trackingLink->is_active]);

        return response()->json([
            'message' => 'Status do link atualizado com sucesso',
            'is_active' => $trackingLink->is_active,
        ]);
    }

    /**
     * Estatísticas do link
     */
    public function stats(Request $request, TrackingLink $trackingLink)
    {
        $this->authorize('view', $trackingLink);

        return response()->json($trackingLink->getStats());
    }

    /**
     * QR Code do link
     */
    public function qrCode(Request $request, TrackingLink $trackingLink)
    {
        $this->authorize('view', $trackingLink);

        return redirect($trackingLink->getQrCodeUrl());
    }

    /**
     * Redirect do link rastreável
     */
    public function redirect(Request $request, string $token)
    {
        $trackingLink = TrackingLink::where('token', $token)->first();

        if (!$trackingLink) {
            abort(404, 'Link não encontrado');
        }

        if (!$trackingLink->isActive()) {
            abort(410, 'Link expirado ou inativo');
        }

        // Incrementar contador de cliques
        $trackingLink->incrementClicks();

        // Armazenar dados de tracking no cookie
        $trackingData = [
            'token' => $token,
            'company_id' => $trackingLink->company_id,
            'utm_source' => $trackingLink->utm_source,
            'utm_campaign' => $trackingLink->utm_campaign,
            'utm_medium' => $trackingLink->utm_medium,
            'utm_term' => $trackingLink->utm_term,
            'utm_content' => $trackingLink->utm_content,
            'referrer' => $request->header('Referer'),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'clicked_at' => now()->toISOString(),
            'origin' => $trackingLink->getOrigin(),
        ];

        $cookieName = config('tracking.snippet.cookie_name', 'visionmetrics_tracking');
        $cookieDuration = config('tracking.cookie_duration', 30) * 24 * 60; // minutos

        // Gerar URL de destino com UTMs
        $destinationUrl = $trackingLink->getDestinationUrlWithUtm();

        return redirect($destinationUrl)
            ->withCookie(cookie($cookieName, json_encode($trackingData), $cookieDuration));
    }

    /**
     * Capturar dados de tracking via JavaScript
     */
    public function capture(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'tracking_token' => 'nullable|string',
            'utm_source' => 'nullable|string',
            'utm_campaign' => 'nullable|string',
            'utm_medium' => 'nullable|string',
            'utm_term' => 'nullable|string',
            'utm_content' => 'nullable|string',
            'referrer' => 'nullable|string',
            'page_url' => 'nullable|string',
        ]);

        try {
            // Se tem token de tracking, buscar dados do link
            if ($request->tracking_token) {
                $trackingLink = TrackingLink::where('token', $request->tracking_token)
                    ->active()
                    ->first();

                if ($trackingLink) {
                    // Criar ou atualizar lead
                    $lead = $this->createOrUpdateLead($request, $trackingLink);

                    return response()->json([
                        'success' => true,
                        'lead_id' => $lead->id,
                        'message' => 'Dados de tracking capturados com sucesso',
                    ]);
                }
            }

            // Captura geral sem link específico
            $this->captureGeneralTracking($request);

            return response()->json([
                'success' => true,
                'message' => 'Dados de tracking capturados',
            ]);

        } catch (\Exception $e) {
            logger()->error('Error capturing tracking data', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao capturar dados de tracking',
            ], 500);
        }
    }

    /**
     * Pixel de tracking (1x1 transparente)
     */
    public function pixel(Request $request)
    {
        // Log do acesso ao pixel
        logger()->info('Tracking pixel accessed', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('Referer'),
        ]);

        // Retornar pixel transparente 1x1
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        
        return response($pixel)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Script de tracking JavaScript
     */
    public function trackingScript(Request $request)
    {
        $config = config('tracking.snippet');
        
        $autoCapture = $config['auto_capture'] ? 'true' : 'false';
        
        $script = <<<JS
(function() {
    'use strict';
    
    // Configuração
    var config = {
        cookieName: '{$config['cookie_name']}',
        localStorageKey: '{$config['local_storage_key']}',
        apiEndpoint: '{$config['api_endpoint']}',
        autCapture: {$autoCapture}
    };
    
    // Função para obter parâmetros UTM da URL
    function getUTMParams() {
        var params = new URLSearchParams(window.location.search);
        return {
            utm_source: params.get('utm_source'),
            utm_campaign: params.get('utm_campaign'),
            utm_medium: params.get('utm_medium'),
            utm_term: params.get('utm_term'),
            utm_content: params.get('utm_content'),
            gclid: params.get('gclid'),
            fbclid: params.get('fbclid')
        };
    }
    
    // Função para obter dados do cookie de tracking
    function getTrackingData() {
        var cookies = document.cookie.split(';');
        for (var i = 0; i < cookies.length; i++) {
            var cookie = cookies[i].trim();
            if (cookie.indexOf(config.cookieName + '=') === 0) {
                try {
                    return JSON.parse(cookie.substring(config.cookieName.length + 1));
                } catch (e) {
                    return null;
                }
            }
        }
        return null;
    }
    
    // Função para capturar dados de attribution
    function captureAttribution(phone) {
        var trackingData = getTrackingData();
        var utmParams = getUTMParams();
        
        var data = {
            phone: phone,
            tracking_token: trackingData ? trackingData.token : null,
            utm_source: utmParams.utm_source || (trackingData ? trackingData.utm_source : null),
            utm_campaign: utmParams.utm_campaign || (trackingData ? trackingData.utm_campaign : null),
            utm_medium: utmParams.utm_medium || (trackingData ? trackingData.utm_medium : null),
            utm_term: utmParams.utm_term || (trackingData ? trackingData.utm_term : null),
            utm_content: utmParams.utm_content || (trackingData ? trackingData.utm_content : null),
            gclid: utmParams.gclid,
            fbclid: utmParams.fbclid,
            referrer: document.referrer,
            page_url: window.location.href
        };
        
        // Enviar dados para API
        fetch(config.apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        }).catch(function(error) {
            console.error('VisionMetrics tracking error:', error);
        });
    }
    
    // Exposição global
    window.VisionMetrics = {
        capture: captureAttribution
    };
    
    // Auto-captura em cliques de WhatsApp
    if (config.autCapture) {
        document.addEventListener('click', function(e) {
            var link = e.target.closest('a');
            if (link && link.href.includes('wa.me/')) {
                var phone = link.href.match(/wa\.me\/(\d+)/);
                if (phone) {
                    captureAttribution(phone[1]);
                }
            }
        });
    }
})();
JS;

        return response($script)
            ->header('Content-Type', 'application/javascript')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Criar ou atualizar lead com dados de tracking
     */
    private function createOrUpdateLead(Request $request, TrackingLink $trackingLink): Lead
    {
        $leadData = [
            'company_id' => $trackingLink->company_id,
            'phone' => $request->phone,
            'origin' => $trackingLink->getOrigin(),
            'utm_source' => $request->utm_source ?? $trackingLink->utm_source,
            'utm_campaign' => $request->utm_campaign ?? $trackingLink->utm_campaign,
            'utm_medium' => $request->utm_medium ?? $trackingLink->utm_medium,
            'utm_term' => $request->utm_term ?? $trackingLink->utm_term,
            'utm_content' => $request->utm_content ?? $trackingLink->utm_content,
            'tracking_token' => $trackingLink->token,
            'referrer_url' => $request->referrer,
            'attribution_data' => [
                'gclid' => $request->gclid,
                'fbclid' => $request->fbclid,
                'page_url' => $request->page_url,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'captured_at' => now()->toISOString(),
            ],
        ];

        // Buscar lead existente
        $lead = Lead::where('phone', $request->phone)
            ->where('company_id', $trackingLink->company_id)
            ->first();

        if ($lead) {
            // Atualizar apenas se não tinha tracking antes
            if ($lead->origin === 'nao_rastreada') {
                $lead->update($leadData);
                $trackingLink->incrementConversions();
            }
        } else {
            $lead = Lead::create(array_merge($leadData, [
                'first_contact_at' => now(),
                'last_message_at' => now(),
            ]));
            $trackingLink->incrementConversions();
        }

        return $lead;
    }

    /**
     * Capturar tracking geral (sem link específico)
     */
    private function captureGeneralTracking(Request $request): void
    {
        // Armazenar em cache temporariamente até associar com uma empresa
        $cacheKey = 'tracking_' . md5($request->phone . $request->ip());
        
        Cache::put($cacheKey, [
            'phone' => $request->phone,
            'utm_source' => $request->utm_source,
            'utm_campaign' => $request->utm_campaign,
            'utm_medium' => $request->utm_medium,
            'utm_term' => $request->utm_term,
            'utm_content' => $request->utm_content,
            'referrer' => $request->referrer,
            'page_url' => $request->page_url,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'captured_at' => now()->toISOString(),
        ], 3600); // 1 hora
    }
}
