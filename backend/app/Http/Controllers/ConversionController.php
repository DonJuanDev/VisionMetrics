<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversion;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Lead;
use App\Models\AuditLog;

class ConversionController extends Controller
{
    /**
     * Listar conversões
     */
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $query = Conversion::forCompany($companyId)
            ->with(['conversation.lead', 'confirmedBy']);

        // Filtros
        if ($request->status) {
            $query->byStatus($request->status);
        }

        if ($request->detected_by) {
            $query->detectedBy($request->detected_by);
        }

        if ($request->payment_method) {
            $query->byPaymentMethod($request->payment_method);
        }

        if ($request->min_value) {
            $query->where('value', '>=', $request->min_value);
        }

        if ($request->max_value) {
            $query->where('value', '<=', $request->max_value);
        }

        if ($request->date_from) {
            $query->whereDate('detected_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('detected_at', '<=', $request->date_to);
        }

        if ($request->search) {
            $search = $request->search;
            $query->whereHas('conversation.lead', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $conversions = $query->orderBy('detected_at', 'desc')->paginate(20);

        return response()->json($conversions);
    }

    /**
     * Exibir conversão específica
     */
    public function show(Request $request, Conversion $conversion)
    {
        $this->authorize('view-company-data', $conversion->company_id);
        
        $conversion->load([
            'conversation.lead',
            'conversation.messages' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(10);
            },
            'confirmedBy'
        ]);

        return response()->json($conversion);
    }

    /**
     * Criar conversão manual
     */
    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'value' => 'required|numeric|min:0.01|max:999999.99',
            'currency' => 'nullable|string|in:BRL,USD,EUR',
            'payment_method' => 'nullable|in:pix,boleto,cartao_credito,cartao_debito,transferencia,dinheiro,outro',
            'notes' => 'nullable|string|max:1000',
        ]);

        $conversation = Conversation::forCompany($request->user()->company_id)
            ->findOrFail($request->conversation_id);

        // Verificar se já existe conversão confirmada para esta conversa
        $existingConversion = $conversation->conversions()
            ->where('status', 'confirmed')
            ->first();

        if ($existingConversion) {
            return response()->json([
                'error' => 'Esta conversa já possui uma conversão confirmada',
            ], 422);
        }

        $conversion = Conversion::create([
            'conversation_id' => $conversation->id,
            'company_id' => $request->user()->company_id,
            'lead_id' => $conversation->lead_id,
            'value' => $request->value,
            'currency' => $request->currency ?? 'BRL',
            'payment_method' => $request->payment_method,
            'detected_by' => 'manual',
            'status' => 'confirmed', // Manual é confirmado imediatamente
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
            'detected_at' => now(),
            'notes' => $request->notes,
        ]);

        // Marcar conversa e lead como convertidos
        $conversation->markAsConverted();

        return response()->json([
            'message' => 'Conversão criada com sucesso',
            'conversion' => $conversion->load(['conversation.lead', 'confirmedBy']),
        ], 201);
    }

    /**
     * Confirmar conversão (para conversões detectadas automaticamente)
     */
    public function confirm(Request $request, Conversion $conversion)
    {
        $this->authorize('view-company-data', $conversion->company_id);
        $this->authorize('manage-conversions');

        if ($conversion->status === 'confirmed') {
            return response()->json([
                'error' => 'Esta conversão já foi confirmada',
            ], 422);
        }

        if ($conversion->status === 'cancelled') {
            return response()->json([
                'error' => 'Esta conversão foi cancelada e não pode ser confirmada',
            ], 422);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|in:pix,boleto,cartao_credito,cartao_debito,transferencia,dinheiro,outro',
            'value' => 'nullable|numeric|min:0.01|max:999999.99',
        ]);

        // Atualizar dados se fornecidos
        $updateData = [
            'status' => 'confirmed',
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
        ];

        if ($request->has('notes')) {
            $updateData['notes'] = $request->notes;
        }

        if ($request->has('payment_method')) {
            $updateData['payment_method'] = $request->payment_method;
        }

        if ($request->has('value')) {
            $updateData['value'] = $request->value;
        }

        $conversion->update($updateData);

        // Marcar conversa e lead como convertidos
        $conversion->conversation->markAsConverted();

        return response()->json([
            'message' => 'Conversão confirmada com sucesso',
            'conversion' => $conversion->fresh(['conversation.lead', 'confirmedBy']),
        ]);
    }

    /**
     * Cancelar conversão
     */
    public function cancel(Request $request, Conversion $conversion)
    {
        $this->authorize('view-company-data', $conversion->company_id);
        $this->authorize('manage-conversions');

        if ($conversion->status === 'cancelled') {
            return response()->json([
                'error' => 'Esta conversão já foi cancelada',
            ], 422);
        }

        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $conversion->update([
            'status' => 'cancelled',
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Conversão cancelada com sucesso',
            'conversion' => $conversion->fresh(['conversation.lead', 'confirmedBy']),
        ]);
    }

    /**
     * Detectar conversão a partir de mensagem
     */
    public function detectFromMessage(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:messages,id',
        ]);

        $message = Message::forCompany($request->user()->company_id)
            ->with('conversation.lead')
            ->findOrFail($request->message_id);

        // Verificar se a mensagem é do cliente
        if ($message->sender !== 'client') {
            return response()->json([
                'error' => 'Só é possível detectar conversões em mensagens de clientes',
            ], 422);
        }

        // Verificar se já foi analisada
        if ($message->is_parsed) {
            return response()->json([
                'error' => 'Esta mensagem já foi analisada',
            ], 422);
        }

        // Processar mensagem para detecção
        $this->processMessageForConversion($message);

        $message->refresh();

        if ($message->parsed_value && $message->parsed_value > 0) {
            // Verificar se já existe conversão para esta conversa
            $existingConversion = $message->conversation->conversions()
                ->where('status', '!=', 'cancelled')
                ->first();

            if (!$existingConversion) {
                // Criar conversão automática
                $conversion = $this->createAutomaticConversion($message);
                
                return response()->json([
                    'message' => 'Conversão detectada com sucesso',
                    'conversion' => $conversion->load(['conversation.lead', 'confirmedBy']),
                    'detected_value' => $message->parsed_value,
                    'confidence' => $message->nlp_data['confidence'] ?? 0,
                ]);
            } else {
                return response()->json([
                    'message' => 'Valor detectado mas conversa já possui conversão',
                    'existing_conversion' => $existingConversion,
                    'detected_value' => $message->parsed_value,
                ]);
            }
        }

        return response()->json([
            'message' => 'Nenhuma conversão detectada nesta mensagem',
            'analyzed' => true,
        ]);
    }

    /**
     * Atualizar conversão
     */
    public function update(Request $request, Conversion $conversion)
    {
        $this->authorize('view-company-data', $conversion->company_id);
        $this->authorize('manage-conversions');

        $request->validate([
            'value' => 'required|numeric|min:0.01|max:999999.99',
            'currency' => 'nullable|string|in:BRL,USD,EUR',
            'payment_method' => 'nullable|in:pix,boleto,cartao_credito,cartao_debito,transferencia,dinheiro,outro',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldValues = $conversion->toArray();

        $conversion->update($request->only([
            'value', 'currency', 'payment_method', 'notes'
        ]));

        // Log da alteração
        AuditLog::create([
            'company_id' => $conversion->company_id,
            'user_id' => $request->user()->id,
            'event' => 'conversion.updated',
            'auditable_type' => Conversion::class,
            'auditable_id' => $conversion->id,
            'old_values' => $oldValues,
            'new_values' => $conversion->getChanges(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Conversão atualizada com sucesso',
            'conversion' => $conversion->fresh(['conversation.lead', 'confirmedBy']),
        ]);
    }

    /**
     * Remover conversão
     */
    public function destroy(Request $request, Conversion $conversion)
    {
        $this->authorize('view-company-data', $conversion->company_id);
        $this->authorize('manage-conversions');

        // Log da remoção
        AuditLog::create([
            'company_id' => $conversion->company_id,
            'user_id' => $request->user()->id,
            'event' => 'conversion.deleted',
            'auditable_type' => Conversion::class,
            'auditable_id' => $conversion->id,
            'old_values' => $conversion->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $conversion->delete();

        return response()->json([
            'message' => 'Conversão removida com sucesso',
        ]);
    }

    /**
     * Processar mensagem para detecção de conversão
     */
    private function processMessageForConversion(Message $message): void
    {
        if (!$message->body) {
            $message->update(['is_parsed' => true]);
            return;
        }

        $patterns = config('whatsapp.conversion_patterns.value_patterns', []);
        $value = null;
        $currency = 'BRL';

        // Detectar valores
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message->body, $matches)) {
                $valueStr = $matches[1];
                // Converter formato brasileiro para float
                $value = (float) str_replace(['.', ','], ['', '.'], $valueStr);
                break;
            }
        }

        // Detectar keywords e método de pagamento
        $nlpData = $message->detectConversionKeywords();

        $message->update([
            'is_parsed' => true,
            'parsed_value' => $value,
            'parsed_currency' => $currency,
            'nlp_data' => $nlpData,
        ]);
    }

    /**
     * Criar conversão automática
     */
    private function createAutomaticConversion(Message $message): Conversion
    {
        $nlpData = $message->nlp_data ?? [];
        
        return Conversion::create([
            'conversation_id' => $message->conversation_id,
            'company_id' => $message->company_id,
            'lead_id' => $message->conversation->lead_id,
            'value' => $message->parsed_value,
            'currency' => $message->parsed_currency ?? 'BRL',
            'payment_method' => $nlpData['payment_method'] ?? null,
            'detected_by' => 'nlp',
            'detection_data' => [
                'message_id' => $message->id,
                'nlp_data' => $nlpData,
                'confidence' => $nlpData['confidence'] ?? 0,
                'keywords_found' => $nlpData['conversion_keywords'] ?? [],
            ],
            'status' => 'pending', // Precisa confirmação manual
            'detected_at' => now(),
        ]);
    }
}
