<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\AuditLog;

class LeadController extends Controller
{
    /**
     * Listar leads
     */
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $query = Lead::forCompany($companyId)
            ->with(['conversations', 'conversions']);

        // Filtros
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->origin) {
            $query->where('origin', $request->origin);
        }

        if ($request->has('tracked')) {
            if ($request->tracked === 'true' || $request->tracked === '1') {
                $query->tracked();
            } else {
                $query->untracked();
            }
        }

        if ($request->tag) {
            $query->whereJsonContains('tags', $request->tag);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($leads);
    }

    /**
     * Exibir lead específico
     */
    public function show(Request $request, Lead $lead)
    {
        $this->authorize('view-company-data', $lead->company_id);
        
        $lead->load([
            'conversations.messages' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(5);
            },
            'conversions' => function($q) {
                $q->orderBy('created_at', 'desc');
            }
        ]);

        return response()->json($lead);
    }

    /**
     * Criar lead
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'origin' => 'required|in:meta,google,outras,nao_rastreada',
            'source_url' => 'nullable|url|max:1000',
            'utm_source' => 'nullable|string|max:255',
            'utm_medium' => 'nullable|string|max:255',
            'utm_campaign' => 'nullable|string|max:255',
            'utm_content' => 'nullable|string|max:255',
            'utm_term' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $lead = Lead::create([
            'company_id' => $request->user()->company_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'origin' => $request->origin,
            'source_url' => $request->source_url,
            'utm_source' => $request->utm_source,
            'utm_medium' => $request->utm_medium,
            'utm_campaign' => $request->utm_campaign,
            'utm_content' => $request->utm_content,
            'utm_term' => $request->utm_term,
            'tags' => $request->tags ?? [],
            'notes' => $request->notes,
            'status' => 'new',
        ]);

        return response()->json([
            'message' => 'Lead criado com sucesso',
            'lead' => $lead,
        ], 201);
    }

    /**
     * Atualizar lead
     */
    public function update(Request $request, Lead $lead)
    {
        $this->authorize('view-company-data', $lead->company_id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:50',
            'email' => 'sometimes|nullable|email|max:255',
            'origin' => 'sometimes|in:meta,google,outras,nao_rastreada',
            'source_url' => 'sometimes|nullable|url|max:1000',
            'utm_source' => 'sometimes|nullable|string|max:255',
            'utm_medium' => 'sometimes|nullable|string|max:255',
            'utm_campaign' => 'sometimes|nullable|string|max:255',
            'utm_content' => 'sometimes|nullable|string|max:255',
            'utm_term' => 'sometimes|nullable|string|max:255',
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'string|max:50',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        $oldValues = $lead->toArray();
        $lead->update($request->only([
            'name', 'phone', 'email', 'origin', 'source_url',
            'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term',
            'tags', 'notes'
        ]));

        // Log da alteração
        AuditLog::create([
            'company_id' => $lead->company_id,
            'user_id' => $request->user()->id,
            'event' => 'lead.updated',
            'auditable_type' => Lead::class,
            'auditable_id' => $lead->id,
            'old_values' => $oldValues,
            'new_values' => $lead->getChanges(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Lead atualizado com sucesso',
            'lead' => $lead,
        ]);
    }

    /**
     * Remover lead
     */
    public function destroy(Request $request, Lead $lead)
    {
        $this->authorize('view-company-data', $lead->company_id);

        // Verificar se tem conversas associadas
        if ($lead->conversations()->count() > 0) {
            return response()->json([
                'error' => 'Não é possível remover lead com conversas associadas',
            ], 422);
        }

        // Log da remoção
        AuditLog::create([
            'company_id' => $lead->company_id,
            'user_id' => $request->user()->id,
            'event' => 'lead.deleted',
            'auditable_type' => Lead::class,
            'auditable_id' => $lead->id,
            'old_values' => $lead->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $lead->delete();

        return response()->json([
            'message' => 'Lead removido com sucesso',
        ]);
    }

    /**
     * Adicionar tag ao lead
     */
    public function addTag(Request $request, Lead $lead)
    {
        $this->authorize('view-company-data', $lead->company_id);

        $request->validate([
            'tag' => 'required|string|max:50',
        ]);

        $tags = $lead->tags ?? [];
        if (!in_array($request->tag, $tags)) {
            $tags[] = $request->tag;
            $lead->update(['tags' => $tags]);
        }

        return response()->json([
            'message' => 'Tag adicionada com sucesso',
            'lead' => $lead,
        ]);
    }

    /**
     * Remover tag do lead
     */
    public function removeTag(Request $request, Lead $lead, $tag)
    {
        $this->authorize('view-company-data', $lead->company_id);

        $tags = $lead->tags ?? [];
        $tags = array_diff($tags, [$tag]);
        $lead->update(['tags' => array_values($tags)]);

        return response()->json([
            'message' => 'Tag removida com sucesso',
            'lead' => $lead,
        ]);
    }

    /**
     * Atualizar status do lead
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $this->authorize('view-company-data', $lead->company_id);

        $request->validate([
            'status' => 'required|in:new,contacted,qualified,converted,lost',
        ]);

        $oldStatus = $lead->status;
        $lead->update(['status' => $request->status]);

        // Log da alteração de status
        AuditLog::create([
            'company_id' => $lead->company_id,
            'user_id' => $request->user()->id,
            'event' => 'lead.status_changed',
            'auditable_type' => Lead::class,
            'auditable_id' => $lead->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $request->status],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Status atualizado com sucesso',
            'lead' => $lead,
        ]);
    }
}

