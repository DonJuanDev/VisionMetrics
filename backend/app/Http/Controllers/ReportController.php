<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Conversation;
use App\Models\Conversion;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ConversionsExport;
use App\Exports\LeadsExport;
use App\Exports\ConversationsExport;

class ReportController extends Controller
{
    /**
     * Relatório de conversões
     */
    public function conversions(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $query = Conversion::forCompany($companyId)
            ->with(['conversation.lead', 'confirmedBy']);

        // Aplicar filtros
        $this->applyConversionsFilters($query, $request);

        $conversions = $query->orderBy('detected_at', 'desc')->paginate(50);

        // Estatísticas do período
        $stats = $this->getConversionsStats($companyId, $request);

        return response()->json([
            'conversions' => $conversions,
            'stats' => $stats,
        ]);
    }

    /**
     * Relatório de leads
     */
    public function leads(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $query = Lead::forCompany($companyId)->withCount('conversions');

        // Aplicar filtros
        $this->applyLeadsFilters($query, $request);

        $leads = $query->orderBy('created_at', 'desc')->paginate(50);

        // Estatísticas do período
        $stats = $this->getLeadsStats($companyId, $request);

        return response()->json([
            'leads' => $leads,
            'stats' => $stats,
        ]);
    }

    /**
     * Relatório de conversas
     */
    public function conversations(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $query = Conversation::forCompany($companyId)
            ->with(['lead', 'assignedAgent'])
            ->withCount(['messages', 'conversions']);

        // Aplicar filtros
        $this->applyConversationsFilters($query, $request);

        $conversations = $query->orderBy('last_activity_at', 'desc')->paginate(50);

        // Estatísticas do período
        $stats = $this->getConversationsStats($companyId, $request);

        return response()->json([
            'conversations' => $conversations,
            'stats' => $stats,
        ]);
    }

    /**
     * Relatório de performance
     */
    public function performance(Request $request)
    {
        $companyId = $request->user()->company_id;
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Performance por origem
        $performanceByOrigin = $this->getPerformanceByOrigin($companyId, $startDate, $endDate);

        // Performance por agente
        $performanceByAgent = $this->getPerformanceByAgent($companyId, $startDate, $endDate);

        // Performance por período (diária)
        $dailyPerformance = $this->getDailyPerformance($companyId, $startDate, $endDate);

        // Funil de conversão
        $conversionFunnel = $this->getConversionFunnel($companyId, $startDate, $endDate);

        return response()->json([
            'performance_by_origin' => $performanceByOrigin,
            'performance_by_agent' => $performanceByAgent,
            'daily_performance' => $dailyPerformance,
            'conversion_funnel' => $conversionFunnel,
        ]);
    }

    /**
     * Relatório de atribuição
     */
    public function attribution(Request $request)
    {
        $companyId = $request->user()->company_id;
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Leads por origem
        $leadsByOrigin = $this->getLeadsByOrigin($companyId, $startDate, $endDate);

        // UTM analysis
        $utmAnalysis = $this->getUTMAnalysis($companyId, $startDate, $endDate);

        // First/Last touch attribution
        $attributionModels = $this->getAttributionModels($companyId, $startDate, $endDate);

        return response()->json([
            'leads_by_origin' => $leadsByOrigin,
            'utm_analysis' => $utmAnalysis,
            'attribution_models' => $attributionModels,
        ]);
    }

    /**
     * Exportar conversões para Excel/CSV
     */
    public function exportConversions(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,csv',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $companyId = $request->user()->company_id;
        
        $query = Conversion::forCompany($companyId)
            ->with(['conversation.lead', 'confirmedBy']);

        $this->applyConversionsFilters($query, $request);

        $filename = 'conversoes_' . now()->format('Y-m-d_H-i-s') . '.' . $request->format;

        return Excel::download(
            new ConversionsExport($query->get()),
            $filename
        );
    }

    /**
     * Exportar leads para Excel/CSV
     */
    public function exportLeads(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,csv',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $companyId = $request->user()->company_id;
        
        $query = Lead::forCompany($companyId)->with('conversions');

        $this->applyLeadsFilters($query, $request);

        $filename = 'leads_' . now()->format('Y-m-d_H-i-s') . '.' . $request->format;

        return Excel::download(
            new LeadsExport($query->get()),
            $filename
        );
    }

    /**
     * Exportar conversas para Excel/CSV
     */
    public function exportConversations(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,csv',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $companyId = $request->user()->company_id;
        
        $query = Conversation::forCompany($companyId)
            ->with(['lead', 'assignedAgent'])
            ->withCount(['messages', 'conversions']);

        $this->applyConversationsFilters($query, $request);

        $filename = 'conversas_' . now()->format('Y-m-d_H-i-s') . '.' . $request->format;

        return Excel::download(
            new ConversationsExport($query->get()),
            $filename
        );
    }

    // ========== MÉTODOS AUXILIARES ==========

    private function applyConversionsFilters($query, $request)
    {
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->detected_by) {
            $query->where('detected_by', $request->detected_by);
        }

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->min_value) {
            $query->where('value', '>=', $request->min_value);
        }

        if ($request->max_value) {
            $query->where('value', '<=', $request->max_value);
        }

        if ($request->start_date) {
            $query->whereDate('detected_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('detected_at', '<=', $request->end_date);
        }

        if ($request->origin) {
            $query->whereHas('lead', function($q) use ($request) {
                $q->where('origin', $request->origin);
            });
        }
    }

    private function applyLeadsFilters($query, $request)
    {
        if ($request->origin) {
            $query->where('origin', $request->origin);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->utm_source) {
            $query->where('utm_source', 'like', "%{$request->utm_source}%");
        }

        if ($request->utm_campaign) {
            $query->where('utm_campaign', 'like', "%{$request->utm_campaign}%");
        }

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
    }

    private function applyConversationsFilters($query, $request)
    {
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->origin) {
            $query->whereHas('lead', function($q) use ($request) {
                $q->where('origin', $request->origin);
            });
        }

        if ($request->start_date) {
            $query->whereDate('started_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('started_at', '<=', $request->end_date);
        }
    }

    private function getConversionsStats($companyId, $request)
    {
        $query = Conversion::forCompany($companyId);
        $this->applyConversionsFilters($query, $request);

        $total = $query->count();
        $confirmed = $query->where('status', 'confirmed')->count();
        $pending = $query->where('status', 'pending')->count();
        $totalValue = $query->where('status', 'confirmed')->sum('value');
        $avgValue = $confirmed > 0 ? $totalValue / $confirmed : 0;

        return [
            'total' => $total,
            'confirmed' => $confirmed,
            'pending' => $pending,
            'cancelled' => $total - $confirmed - $pending,
            'total_value' => $totalValue,
            'avg_value' => $avgValue,
            'formatted_total_value' => 'R$ ' . number_format($totalValue, 2, ',', '.'),
            'formatted_avg_value' => 'R$ ' . number_format($avgValue, 2, ',', '.'),
        ];
    }

    private function getLeadsStats($companyId, $request)
    {
        $query = Lead::forCompany($companyId);
        $this->applyLeadsFilters($query, $request);

        $total = $query->count();
        $tracked = $query->tracked()->count();
        $converted = $query->where('status', 'converted')->count();
        
        return [
            'total' => $total,
            'tracked' => $tracked,
            'untracked' => $total - $tracked,
            'converted' => $converted,
            'conversion_rate' => $total > 0 ? ($converted / $total) * 100 : 0,
            'tracking_rate' => $total > 0 ? ($tracked / $total) * 100 : 0,
        ];
    }

    private function getConversationsStats($companyId, $request)
    {
        $query = Conversation::forCompany($companyId);
        $this->applyConversationsFilters($query, $request);

        $total = $query->count();
        $active = $query->whereIn('status', ['open', 'qualified'])->count();
        $converted = $query->where('status', 'converted')->count();
        
        return [
            'total' => $total,
            'active' => $active,
            'closed' => $query->where('status', 'closed')->count(),
            'converted' => $converted,
            'lost' => $query->where('status', 'lost')->count(),
            'conversion_rate' => $total > 0 ? ($converted / $total) * 100 : 0,
        ];
    }

    private function getPerformanceByOrigin($companyId, $startDate, $endDate)
    {
        return DB::table('leads')
            ->leftJoin('conversions', function($join) {
                $join->on('leads.id', '=', 'conversions.lead_id')
                     ->where('conversions.status', '=', 'confirmed');
            })
            ->where('leads.company_id', $companyId)
            ->whereBetween('leads.created_at', [$startDate, $endDate])
            ->select([
                'leads.origin',
                DB::raw('COUNT(DISTINCT leads.id) as leads_count'),
                DB::raw('COUNT(conversions.id) as conversions_count'),
                DB::raw('COALESCE(SUM(conversions.value), 0) as total_value'),
                DB::raw('CASE WHEN COUNT(DISTINCT leads.id) > 0 THEN 
                    ROUND((COUNT(conversions.id) / COUNT(DISTINCT leads.id)) * 100, 2) 
                    ELSE 0 END as conversion_rate')
            ])
            ->groupBy('leads.origin')
            ->get();
    }

    private function getPerformanceByAgent($companyId, $startDate, $endDate)
    {
        return DB::table('conversations')
            ->join('users', 'conversations.assigned_to', '=', 'users.id')
            ->leftJoin('conversions', function($join) {
                $join->on('conversations.id', '=', 'conversions.conversation_id')
                     ->where('conversions.status', '=', 'confirmed');
            })
            ->where('conversations.company_id', $companyId)
            ->whereBetween('conversations.created_at', [$startDate, $endDate])
            ->whereNotNull('conversations.assigned_to')
            ->select([
                'users.id as agent_id',
                'users.name as agent_name',
                DB::raw('COUNT(DISTINCT conversations.id) as conversations_count'),
                DB::raw('COUNT(conversions.id) as conversions_count'),
                DB::raw('COALESCE(SUM(conversions.value), 0) as total_value'),
                DB::raw('CASE WHEN COUNT(DISTINCT conversations.id) > 0 THEN 
                    ROUND((COUNT(conversions.id) / COUNT(DISTINCT conversations.id)) * 100, 2) 
                    ELSE 0 END as conversion_rate')
            ])
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_value', 'desc')
            ->get();
    }

    private function getDailyPerformance($companyId, $startDate, $endDate)
    {
        $leads = DB::table('leads')
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as leads_count')
            ])
            ->groupBy('date')
            ->pluck('leads_count', 'date');

        $conversions = DB::table('conversions')
            ->where('company_id', $companyId)
            ->where('status', 'confirmed')
            ->whereBetween('confirmed_at', [$startDate, $endDate])
            ->select([
                DB::raw('DATE(confirmed_at) as date'),
                DB::raw('COUNT(*) as conversions_count'),
                DB::raw('SUM(value) as total_value')
            ])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        for ($date = $start; $date <= $end; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $conversionData = $conversions->get($dateStr);
            
            $result[] = [
                'date' => $dateStr,
                'leads' => $leads->get($dateStr, 0),
                'conversions' => $conversionData->conversions_count ?? 0,
                'revenue' => $conversionData->total_value ?? 0,
            ];
        }

        return $result;
    }

    private function getConversionFunnel($companyId, $startDate, $endDate)
    {
        $leads = Lead::forCompany($companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $contacted = Lead::forCompany($companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['contacted', 'qualified', 'converted'])
            ->count();

        $qualified = Lead::forCompany($companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['qualified', 'converted'])
            ->count();

        $converted = Lead::forCompany($companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'converted')
            ->count();

        return [
            ['stage' => 'Leads', 'count' => $leads, 'percentage' => 100],
            ['stage' => 'Contatados', 'count' => $contacted, 'percentage' => $leads > 0 ? ($contacted / $leads) * 100 : 0],
            ['stage' => 'Qualificados', 'count' => $qualified, 'percentage' => $leads > 0 ? ($qualified / $leads) * 100 : 0],
            ['stage' => 'Convertidos', 'count' => $converted, 'percentage' => $leads > 0 ? ($converted / $leads) * 100 : 0],
        ];
    }

    private function getLeadsByOrigin($companyId, $startDate, $endDate)
    {
        return Lead::forCompany($companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'origin',
                DB::raw('COUNT(*) as count'),
                DB::raw('ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER(), 2) as percentage')
            ])
            ->groupBy('origin')
            ->orderBy('count', 'desc')
            ->get();
    }

    private function getUTMAnalysis($companyId, $startDate, $endDate)
    {
        $sources = Lead::forCompany($companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('utm_source')
            ->select('utm_source', DB::raw('COUNT(*) as count'))
            ->groupBy('utm_source')
            ->orderBy('count', 'desc')
            ->get();

        $campaigns = Lead::forCompany($companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('utm_campaign')
            ->select('utm_campaign', DB::raw('COUNT(*) as count'))
            ->groupBy('utm_campaign')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'sources' => $sources,
            'campaigns' => $campaigns,
        ];
    }

    private function getAttributionModels($companyId, $startDate, $endDate)
    {
        // Este é um exemplo simplificado - em produção seria mais complexo
        return [
            'first_touch' => 'Primeira interação recebe 100% do crédito',
            'last_touch' => 'Última interação recebe 100% do crédito',
            'linear' => 'Crédito dividido igualmente entre todas as interações',
        ];
    }
}
