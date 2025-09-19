<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Conversation;

class MessageController extends Controller
{
    /**
     * Listar mensagens de uma conversa
     */
    public function index(Request $request, Conversation $conversation)
    {
        $this->authorize('view-company-data', $conversation->company_id);
        
        $query = $conversation->messages()->with(['sentBy']);

        // Paginação
        $perPage = min($request->get('per_page', 50), 100);
        
        if ($request->before) {
            $query->where('id', '<', $request->before);
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Reverter ordem para exibição cronológica
        $messages->getCollection()->transform(function ($message) {
            return $message;
        });

        return response()->json($messages);
    }

    /**
     * Enviar mensagem
     */
    public function store(Request $request, Conversation $conversation)
    {
        $this->authorize('view-company-data', $conversation->company_id);

        $request->validate([
            'body' => 'required|string|max:4000',
            'message_type' => 'nullable|in:text,image,audio,video,document',
            'media_url' => 'nullable|url|max:1000',
            'media_filename' => 'nullable|string|max:255',
        ]);

        // Verificar se a conversa está ativa
        if ($conversation->status === 'closed') {
            return response()->json([
                'error' => 'Não é possível enviar mensagens para conversas fechadas',
            ], 422);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'company_id' => $conversation->company_id,
            'lead_id' => $conversation->lead_id,
            'sender' => 'agent',
            'sent_by' => $request->user()->id,
            'body' => $request->body,
            'message_type' => $request->message_type ?? 'text',
            'media_url' => $request->media_url,
            'media_filename' => $request->media_filename,
            'sent_at' => now(),
        ]);

        // Atualizar última atividade da conversa
        $conversation->update([
            'last_activity_at' => now(),
            'last_message_at' => now(),
        ]);

        // Se não estiver atribuída, atribuir ao usuário atual
        if (!$conversation->assigned_to) {
            $conversation->update([
                'assigned_to' => $request->user()->id,
                'assigned_at' => now(),
            ]);
        }

        // TODO: Enviar mensagem via WhatsApp API
        $this->sendToWhatsApp($message);

        return response()->json([
            'message' => 'Mensagem enviada com sucesso',
            'data' => $message->load(['sentBy']),
        ], 201);
    }

    /**
     * Marcar mensagem como lida
     */
    public function markAsRead(Request $request, Message $message)
    {
        $this->authorize('view-company-data', $message->company_id);

        if ($message->sender === 'client' && !$message->read_at) {
            $message->update(['read_at' => now()]);
            
            // Atualizar contador de não lidas na conversa
            $message->conversation->updateUnreadCount();
        }

        return response()->json([
            'message' => 'Mensagem marcada como lida',
            'data' => $message,
        ]);
    }

    /**
     * Buscar mensagens
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:3|max:255',
            'conversation_id' => 'nullable|exists:conversations,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $companyId = $request->user()->company_id;
        
        $query = Message::forCompany($companyId)
            ->with(['conversation.lead', 'sentBy'])
            ->where('body', 'like', '%' . $request->query . '%');

        if ($request->conversation_id) {
            $query->where('conversation_id', $request->conversation_id);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($messages);
    }

    /**
     * Obter estatísticas de mensagens
     */
    public function stats(Request $request)
    {
        $companyId = $request->user()->company_id;
        $days = $request->get('days', 30);
        
        $startDate = now()->subDays($days);
        
        $stats = [
            'total_messages' => Message::forCompany($companyId)
                ->where('created_at', '>=', $startDate)
                ->count(),
            
            'messages_sent' => Message::forCompany($companyId)
                ->where('created_at', '>=', $startDate)
                ->where('sender', 'agent')
                ->count(),
            
            'messages_received' => Message::forCompany($companyId)
                ->where('created_at', '>=', $startDate)
                ->where('sender', 'client')
                ->count(),
            
            'response_time_avg' => $this->getAverageResponseTime($companyId, $startDate),
            
            'messages_by_day' => $this->getMessagesByDay($companyId, $startDate),
        ];

        return response()->json($stats);
    }

    /**
     * Enviar mensagem via WhatsApp API
     */
    private function sendToWhatsApp(Message $message): void
    {
        // TODO: Implementar integração com WhatsApp Business API
        // Por enquanto apenas simular o envio
        
        $message->update([
            'status' => 'sent',
            'external_id' => 'wamid.' . uniqid(),
        ]);
    }

    /**
     * Calcular tempo médio de resposta
     */
    private function getAverageResponseTime(int $companyId, $startDate): ?float
    {
        // Implementação simplificada - pode ser melhorada
        $conversations = Conversation::forCompany($companyId)
            ->where('created_at', '>=', $startDate)
            ->with(['messages' => function($q) {
                $q->orderBy('created_at', 'asc');
            }])
            ->get();

        $responseTimes = [];
        
        foreach ($conversations as $conversation) {
            $lastClientMessage = null;
            
            foreach ($conversation->messages as $message) {
                if ($message->sender === 'client') {
                    $lastClientMessage = $message;
                } elseif ($message->sender === 'agent' && $lastClientMessage) {
                    $responseTime = $message->created_at->diffInMinutes($lastClientMessage->created_at);
                    $responseTimes[] = $responseTime;
                    $lastClientMessage = null;
                }
            }
        }

        return count($responseTimes) > 0 ? array_sum($responseTimes) / count($responseTimes) : null;
    }

    /**
     * Obter mensagens por dia
     */
    private function getMessagesByDay(int $companyId, $startDate): array
    {
        $messages = Message::forCompany($companyId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, sender, COUNT(*) as count')
            ->groupBy('date', 'sender')
            ->orderBy('date')
            ->get();

        $result = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= now()) {
            $dateStr = $currentDate->format('Y-m-d');
            $result[$dateStr] = [
                'date' => $dateStr,
                'sent' => 0,
                'received' => 0,
                'total' => 0,
            ];
            $currentDate->addDay();
        }

        foreach ($messages as $message) {
            if (isset($result[$message->date])) {
                if ($message->sender === 'agent') {
                    $result[$message->date]['sent'] = $message->count;
                } else {
                    $result[$message->date]['received'] = $message->count;
                }
                $result[$message->date]['total'] += $message->count;
            }
        }

        return array_values($result);
    }
}

