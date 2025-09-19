<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Webhook;
use App\Models\AuditLog;

class WebhookController extends Controller
{
    /**
     * Listar webhooks
     */
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $webhooks = Webhook::forCompany($companyId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($webhooks);
    }

    /**
     * Exibir webhook específico
     */
    public function show(Request $request, Webhook $webhook)
    {
        $this->authorize('view-company-data', $webhook->company_id);

        return response()->json($webhook);
    }

    /**
     * Criar webhook
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:1000',
            'events' => 'required|array|min:1',
            'events.*' => 'in:lead.created,conversation.started,message.received,conversion.confirmed',
            'secret' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $webhook = Webhook::create([
            'company_id' => $request->user()->company_id,
            'name' => $request->name,
            'url' => $request->url,
            'events' => $request->events,
            'secret' => $request->secret,
            'active' => $request->get('active', true),
        ]);

        return response()->json([
            'message' => 'Webhook criado com sucesso',
            'webhook' => $webhook,
        ], 201);
    }

    /**
     * Atualizar webhook
     */
    public function update(Request $request, Webhook $webhook)
    {
        $this->authorize('view-company-data', $webhook->company_id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'url' => 'sometimes|url|max:1000',
            'events' => 'sometimes|array|min:1',
            'events.*' => 'in:lead.created,conversation.started,message.received,conversion.confirmed',
            'secret' => 'sometimes|nullable|string|max:255',
            'active' => 'sometimes|boolean',
        ]);

        $webhook->update($request->only([
            'name', 'url', 'events', 'secret', 'active'
        ]));

        return response()->json([
            'message' => 'Webhook atualizado com sucesso',
            'webhook' => $webhook,
        ]);
    }

    /**
     * Remover webhook
     */
    public function destroy(Request $request, Webhook $webhook)
    {
        $this->authorize('view-company-data', $webhook->company_id);

        $webhook->delete();

        return response()->json([
            'message' => 'Webhook removido com sucesso',
        ]);
    }

    /**
     * Ativar/desativar webhook
     */
    public function toggle(Request $request, Webhook $webhook)
    {
        $this->authorize('view-company-data', $webhook->company_id);

        $webhook->update(['active' => !$webhook->active]);

        return response()->json([
            'message' => $webhook->active ? 'Webhook ativado' : 'Webhook desativado',
            'webhook' => $webhook,
        ]);
    }

    /**
     * Testar webhook
     */
    public function test(Request $request, Webhook $webhook)
    {
        $this->authorize('view-company-data', $webhook->company_id);

        // Enviar payload de teste
        $testPayload = [
            'event' => 'webhook.test',
            'timestamp' => now()->toISOString(),
            'data' => [
                'message' => 'Este é um teste do webhook',
                'webhook_id' => $webhook->id,
                'company_id' => $webhook->company_id,
            ]
        ];

        try {
            $response = $this->sendWebhook($webhook, $testPayload);
            
            return response()->json([
                'message' => 'Webhook testado com sucesso',
                'status_code' => $response['status_code'],
                'response_time' => $response['response_time'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Falha no teste do webhook',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Enviar webhook
     */
    private function sendWebhook(Webhook $webhook, array $payload): array
    {
        $startTime = microtime(true);
        
        $headers = [
            'Content-Type: application/json',
            'User-Agent: VisionMetrics/1.0',
        ];

        if ($webhook->secret) {
            $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret);
            $headers[] = 'X-VisionMetrics-Signature: sha256=' . $signature;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $webhook->url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000, 2); // ms

        if ($error) {
            throw new \Exception("cURL Error: {$error}");
        }

        if ($statusCode >= 400) {
            throw new \Exception("HTTP Error: {$statusCode}");
        }

        return [
            'status_code' => $statusCode,
            'response' => $response,
            'response_time' => $responseTime,
        ];
    }
}

