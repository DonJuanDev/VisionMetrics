<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Company;
use App\Models\Lead;
use App\Models\Conversation;
use App\Models\Message;
use App\Jobs\ProcessWhatsAppMessage;

class WhatsAppWebhookController extends Controller
{
    /**
     * Verificação do webhook (GET)
     */
    public function verify(Request $request)
    {
        $verifyToken = config('whatsapp.cloud_api.webhook_verify_token');
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        if ($mode && $token) {
            if ($mode === 'subscribe' && $token === $verifyToken) {
                Log::info('WhatsApp webhook verified successfully');
                return response($challenge, 200);
            }
        }

        Log::warning('WhatsApp webhook verification failed', [
            'mode' => $mode,
            'token' => $token,
            'expected_token' => $verifyToken,
        ]);

        return response('Verification failed', 403);
    }

    /**
     * Processar webhook do WhatsApp (POST)
     */
    public function handle(Request $request)
    {
        try {
            $data = $request->all();
            
            Log::info('WhatsApp webhook received', ['data' => $data]);

            // Verificar se é uma mudança válida
            if (!isset($data['entry']) || !is_array($data['entry'])) {
                return response()->json(['status' => 'ignored'], 200);
            }

            foreach ($data['entry'] as $entry) {
                if (!isset($entry['changes'])) {
                    continue;
                }

                foreach ($entry['changes'] as $change) {
                    if ($change['field'] === 'messages') {
                        $this->processMessagesChange($change['value']);
                    }
                }
            }

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing WhatsApp webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Processar mudanças de mensagens
     */
    private function processMessagesChange(array $data): void
    {
        // Processar mensagens recebidas
        if (isset($data['messages']) && is_array($data['messages'])) {
            foreach ($data['messages'] as $messageData) {
                $this->processIncomingMessage($messageData);
            }
        }

        // Processar status de mensagens enviadas
        if (isset($data['statuses']) && is_array($data['statuses'])) {
            foreach ($data['statuses'] as $statusData) {
                $this->processMessageStatus($statusData);
            }
        }
    }

    /**
     * Processar mensagem recebida
     */
    private function processIncomingMessage(array $messageData): void
    {
        try {
            $phoneNumber = $messageData['from'];
            $whatsappMessageId = $messageData['id'];
            $timestamp = $messageData['timestamp'];

            // Buscar ou criar lead baseado no número de telefone
            $lead = $this->findOrCreateLead($phoneNumber);
            
            if (!$lead) {
                Log::warning('Could not create lead for phone number', [
                    'phone' => $phoneNumber
                ]);
                return;
            }

            // Buscar ou criar conversa
            $conversation = $this->findOrCreateConversation($lead);

            // Extrair conteúdo da mensagem
            $messageContent = $this->extractMessageContent($messageData);

            // Criar mensagem
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'company_id' => $lead->company_id,
                'whatsapp_message_id' => $whatsappMessageId,
                'sender' => 'client',
                'type' => $messageContent['type'],
                'body' => $messageContent['body'],
                'attachments' => $messageContent['attachments'],
                'status' => 'received',
            ]);

            // Atualizar timestamps
            $lead->updateLastMessage();
            $conversation->updateActivity();

            // Processar em background para NLP e automações
            ProcessWhatsAppMessage::dispatch($message);

            Log::info('WhatsApp message processed successfully', [
                'message_id' => $message->id,
                'lead_id' => $lead->id,
                'conversation_id' => $conversation->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing incoming WhatsApp message', [
                'error' => $e->getMessage(),
                'message_data' => $messageData,
            ]);
        }
    }

    /**
     * Buscar ou criar lead baseado no número de telefone
     */
    private function findOrCreateLead(string $phoneNumber): ?Lead
    {
        // Primeiro, tentar encontrar lead existente por telefone
        $lead = Lead::where('phone', $phoneNumber)->first();
        
        if ($lead) {
            return $lead;
        }

        // Se não encontrar, tentar associar com tracking token ativo
        $trackingToken = $this->getActiveTrackingToken($phoneNumber);
        
        if ($trackingToken) {
            // Buscar dados de tracking armazenados
            $trackingData = $this->getTrackingData($trackingToken);
            
            if ($trackingData && isset($trackingData['company_id'])) {
                return Lead::create([
                    'company_id' => $trackingData['company_id'],
                    'phone' => $phoneNumber,
                    'first_contact_at' => now(),
                    'last_message_at' => now(),
                    'origin' => $trackingData['origin'] ?? 'nao_rastreada',
                    'utm_source' => $trackingData['utm_source'] ?? null,
                    'utm_campaign' => $trackingData['utm_campaign'] ?? null,
                    'utm_medium' => $trackingData['utm_medium'] ?? null,
                    'utm_term' => $trackingData['utm_term'] ?? null,
                    'utm_content' => $trackingData['utm_content'] ?? null,
                    'tracking_token' => $trackingToken,
                    'referrer_url' => $trackingData['referrer_url'] ?? null,
                    'attribution_data' => $trackingData['attribution_data'] ?? null,
                ]);
            }
        }

        // Se não conseguir associar com tracking, criar lead não rastreado
        // Para isso, precisaríamos de uma forma de determinar a empresa
        // Por simplicidade, vamos assumir que só há uma empresa ativa ou usar configuração
        
        $defaultCompany = Company::where('is_active', true)->first();
        
        if (!$defaultCompany) {
            return null;
        }

        return Lead::create([
            'company_id' => $defaultCompany->id,
            'phone' => $phoneNumber,
            'first_contact_at' => now(),
            'last_message_at' => now(),
            'origin' => 'nao_rastreada',
        ]);
    }

    /**
     * Buscar ou criar conversa
     */
    private function findOrCreateConversation(Lead $lead): Conversation
    {
        // Buscar conversa ativa existente
        $conversation = $lead->conversations()
            ->where('status', 'open')
            ->first();

        if ($conversation) {
            return $conversation;
        }

        // Criar nova conversa
        return Conversation::create([
            'lead_id' => $lead->id,
            'company_id' => $lead->company_id,
            'started_at' => now(),
            'last_activity_at' => now(),
            'status' => 'open',
            'has_unread' => true,
        ]);
    }

    /**
     * Extrair conteúdo da mensagem
     */
    private function extractMessageContent(array $messageData): array
    {
        $content = [
            'type' => 'text',
            'body' => null,
            'attachments' => null,
        ];

        // Mensagem de texto
        if (isset($messageData['text'])) {
            $content['body'] = $messageData['text']['body'];
        }

        // Imagem
        elseif (isset($messageData['image'])) {
            $content['type'] = 'image';
            $content['body'] = $messageData['image']['caption'] ?? null;
            $content['attachments'] = [
                'image' => [
                    'id' => $messageData['image']['id'],
                    'mime_type' => $messageData['image']['mime_type'] ?? null,
                    'sha256' => $messageData['image']['sha256'] ?? null,
                ]
            ];
        }

        // Documento
        elseif (isset($messageData['document'])) {
            $content['type'] = 'document';
            $content['body'] = $messageData['document']['caption'] ?? $messageData['document']['filename'] ?? null;
            $content['attachments'] = [
                'document' => [
                    'id' => $messageData['document']['id'],
                    'filename' => $messageData['document']['filename'] ?? null,
                    'mime_type' => $messageData['document']['mime_type'] ?? null,
                    'sha256' => $messageData['document']['sha256'] ?? null,
                ]
            ];
        }

        // Áudio
        elseif (isset($messageData['audio'])) {
            $content['type'] = 'audio';
            $content['attachments'] = [
                'audio' => [
                    'id' => $messageData['audio']['id'],
                    'mime_type' => $messageData['audio']['mime_type'] ?? null,
                    'sha256' => $messageData['audio']['sha256'] ?? null,
                ]
            ];
        }

        // Vídeo
        elseif (isset($messageData['video'])) {
            $content['type'] = 'video';
            $content['body'] = $messageData['video']['caption'] ?? null;
            $content['attachments'] = [
                'video' => [
                    'id' => $messageData['video']['id'],
                    'mime_type' => $messageData['video']['mime_type'] ?? null,
                    'sha256' => $messageData['video']['sha256'] ?? null,
                ]
            ];
        }

        return $content;
    }

    /**
     * Processar status de mensagem
     */
    private function processMessageStatus(array $statusData): void
    {
        $whatsappMessageId = $statusData['id'];
        $status = $statusData['status']; // sent, delivered, read, failed

        $message = Message::where('whatsapp_message_id', $whatsappMessageId)->first();

        if ($message) {
            $message->update(['status' => $status]);
        }
    }

    /**
     * Obter token de tracking ativo para um número de telefone
     */
    private function getActiveTrackingToken(string $phoneNumber): ?string
    {
        // Esta implementação dependeria de como você armazena a associação
        // telefone -> token de tracking. Pode ser em cache/sessão/cookie
        
        // Por simplicidade, retornamos null aqui
        // Em produção, você implementaria a lógica real
        return null;
    }

    /**
     * Obter dados de tracking armazenados
     */
    private function getTrackingData(string $token): ?array
    {
        // Buscar dados de tracking no cache/banco baseado no token
        // Por simplicidade, retornamos null aqui
        return null;
    }
}
