<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function handle(): void
    {
        try {
            Log::info('Processing WhatsApp message', [
                'message_id' => $this->message->id,
                'conversation_id' => $this->message->conversation_id,
            ]);

            // 1. Processar NLP para detectar conversões
            if ($this->message->sender === 'client' && $this->message->body) {
                $this->processNLPDetection();
            }

            // 2. Verificar se precisa de follow-up automático
            $this->checkAutoFollowUp();

            // 3. Enviar webhooks se configurado
            $this->triggerWebhooks();

            // 4. Notificar agentes se necessário
            $this->notifyAgents();

            Log::info('WhatsApp message processed successfully', [
                'message_id' => $this->message->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process WhatsApp message', [
                'message_id' => $this->message->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function processNLPDetection(): void
    {
        // Verificar se já foi processada
        if ($this->message->is_parsed) {
            return;
        }

        // Processar para detecção de valores
        $this->message->parseForValues();

        Log::info('NLP processing completed', [
            'message_id' => $this->message->id,
            'parsed_value' => $this->message->parsed_value,
            'is_parsed' => $this->message->is_parsed,
        ]);
    }

    private function checkAutoFollowUp(): void
    {
        $conversation = $this->message->conversation;

        // Verificar se a conversa precisa de follow-up
        if ($conversation->shouldFollowUp()) {
            dispatch(new \App\Jobs\SendAutoFollowUp($conversation))
                ->delay(now()->addHours(config('whatsapp.automation.follow_up_delay_hours', 24)));

            Log::info('Auto follow-up scheduled', [
                'conversation_id' => $conversation->id,
            ]);
        }
    }

    private function triggerWebhooks(): void
    {
        $webhooks = $this->message->company->webhooks()
            ->where('active', true)
            ->get();

        foreach ($webhooks as $webhook) {
            $events = $webhook->events ?? [];
            
            if (in_array('message.received', $events)) {
                dispatch(new \App\Jobs\TriggerWebhook(
                    $webhook,
                    'message.received',
                    $this->formatMessageForWebhook()
                ));
            }
        }
    }

    private function notifyAgents(): void
    {
        $conversation = $this->message->conversation;

        // Notificar agente responsável se houver
        if ($conversation->assignedAgent) {
            // Implementar notificação push/email
            Log::info('Agent notification sent', [
                'agent_id' => $conversation->assignedAgent->id,
                'conversation_id' => $conversation->id,
            ]);
        }
    }

    private function formatMessageForWebhook(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'whatsapp_message_id' => $this->message->whatsapp_message_id,
                'sender' => $this->message->sender,
                'type' => $this->message->type,
                'body' => $this->message->body,
                'created_at' => $this->message->created_at->toISOString(),
            ],
            'conversation' => [
                'id' => $this->message->conversation->id,
                'status' => $this->message->conversation->status,
            ],
            'lead' => [
                'id' => $this->message->conversation->lead->id,
                'phone' => $this->message->conversation->lead->phone,
                'name' => $this->message->conversation->lead->name,
                'origin' => $this->message->conversation->lead->origin,
            ],
        ];
    }
}
