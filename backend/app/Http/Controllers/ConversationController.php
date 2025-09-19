<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\AuditLog;

class ConversationController extends Controller
{
    /**
     * Listar conversas
     */
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $query = Conversation::forCompany($companyId)
            ->with(['lead', 'assignedAgent', 'lastMessage']);

        // Filtros
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->assigned_to) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->has_unread) {
            $query->where('unread_messages_count', '>', 0);
        }

        if ($request->origin) {
            $query->whereHas('lead', function($q) use ($request) {
                $q->where('origin', $request->origin);
            });
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->search) {
            $search = $request->search;
            $query->whereHas('lead', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $conversations = $query->orderBy('last_activity_at', 'desc')->paginate(20);

        return response()->json($conversations);
    }

    /**
     * Exibir conversa específica
     */
    public function show(Request $request, Conversation $conversation)
    {
        $this->authorize('view-company-data', $conversation->company_id);
        
        $conversation->load([
            'lead',
            'assignedAgent',
            'messages' => function($q) {
                $q->orderBy('created_at', 'asc');
            },
            'conversions'
        ]);

        // Marcar como lida se tiver mensagens não lidas
        if ($conversation->unread_messages_count > 0) {
            $conversation->markAsRead();
        }

        return response()->json($conversation);
    }

    /**
     * Criar conversa
     */
    public function store(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'whatsapp_chat_id' => 'nullable|string|max:255',
            'platform' => 'required|in:whatsapp,telegram,instagram,facebook',
        ]);

        // Verificar se o lead pertence à empresa
        $lead = \App\Models\Lead::forCompany($request->user()->company_id)
            ->findOrFail($request->lead_id);

        $conversation = Conversation::create([
            'company_id' => $request->user()->company_id,
            'lead_id' => $lead->id,
            'whatsapp_chat_id' => $request->whatsapp_chat_id,
            'platform' => $request->platform,
            'status' => 'active',
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);

        return response()->json([
            'message' => 'Conversa criada com sucesso',
            'conversation' => $conversation->load(['lead', 'assignedAgent']),
        ], 201);
    }

    /**
     * Atribuir conversa a um agente
     */
    public function assign(Request $request, Conversation $conversation)
    {
        $this->authorize('view-company-data', $conversation->company_id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Verificar se o usuário pertence à mesma empresa
        $user = \App\Models\User::forCompany($request->user()->company_id)
            ->findOrFail($request->user_id);

        $conversation->update([
            'assigned_to' => $user->id,
            'assigned_at' => now(),
        ]);

        // Log da atribuição
        AuditLog::create([
            'company_id' => $conversation->company_id,
            'user_id' => $request->user()->id,
            'event' => 'conversation.assigned',
            'auditable_type' => Conversation::class,
            'auditable_id' => $conversation->id,
            'new_values' => ['assigned_to' => $user->id, 'assigned_to_name' => $user->name],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Conversa atribuída com sucesso',
            'conversation' => $conversation->fresh(['lead', 'assignedAgent']),
        ]);
    }

    /**
     * Desatribuir conversa
     */
    public function unassign(Request $request, Conversation $conversation)
    {
        $this->authorize('view-company-data', $conversation->company_id);

        $oldAssignee = $conversation->assignedAgent;

        $conversation->update([
            'assigned_to' => null,
            'assigned_at' => null,
        ]);

        // Log da desatribuição
        AuditLog::create([
            'company_id' => $conversation->company_id,
            'user_id' => $request->user()->id,
            'event' => 'conversation.unassigned',
            'auditable_type' => Conversation::class,
            'auditable_id' => $conversation->id,
            'old_values' => ['assigned_to' => $oldAssignee?->id, 'assigned_to_name' => $oldAssignee?->name],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Conversa desatribuída com sucesso',
            'conversation' => $conversation->fresh(['lead', 'assignedAgent']),
        ]);
    }

    /**
     * Fechar conversa
     */
    public function close(Request $request, Conversation $conversation)
    {
        $this->authorize('view-company-data', $conversation->company_id);

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $conversation->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $request->user()->id,
            'close_reason' => $request->reason,
        ]);

        return response()->json([
            'message' => 'Conversa fechada com sucesso',
            'conversation' => $conversation->fresh(['lead', 'assignedAgent']),
        ]);
    }

    /**
     * Reabrir conversa
     */
    public function reopen(Request $request, Conversation $conversation)
    {
        $this->authorize('view-company-data', $conversation->company_id);

        $conversation->update([
            'status' => 'active',
            'closed_at' => null,
            'closed_by' => null,
            'close_reason' => null,
            'last_activity_at' => now(),
        ]);

        return response()->json([
            'message' => 'Conversa reaberta com sucesso',
            'conversation' => $conversation->fresh(['lead', 'assignedAgent']),
        ]);
    }

    /**
     * Marcar conversa como lida
     */
    public function markAsRead(Request $request, Conversation $conversation)
    {
        $this->authorize('view-company-data', $conversation->company_id);

        $conversation->markAsRead();

        return response()->json([
            'message' => 'Conversa marcada como lida',
            'conversation' => $conversation->fresh(),
        ]);
    }

    /**
     * Atualizar conversa
     */
    public function update(Request $request, Conversation $conversation)
    {
        $this->authorize('view-company-data', $conversation->company_id);

        $request->validate([
            'notes' => 'sometimes|nullable|string|max:1000',
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $oldValues = $conversation->toArray();
        $conversation->update($request->only(['notes', 'tags']));

        // Log da alteração
        if ($conversation->wasChanged()) {
            AuditLog::create([
                'company_id' => $conversation->company_id,
                'user_id' => $request->user()->id,
                'event' => 'conversation.updated',
                'auditable_type' => Conversation::class,
                'auditable_id' => $conversation->id,
                'old_values' => $oldValues,
                'new_values' => $conversation->getChanges(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json([
            'message' => 'Conversa atualizada com sucesso',
            'conversation' => $conversation,
        ]);
    }

    /**
     * Remover conversa
     */
    public function destroy(Request $request, Conversation $conversation)
    {
        $this->authorize('view-company-data', $conversation->company_id);

        // Log da remoção
        AuditLog::create([
            'company_id' => $conversation->company_id,
            'user_id' => $request->user()->id,
            'event' => 'conversation.deleted',
            'auditable_type' => Conversation::class,
            'auditable_id' => $conversation->id,
            'old_values' => $conversation->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $conversation->delete();

        return response()->json([
            'message' => 'Conversa removida com sucesso',
        ]);
    }
}

