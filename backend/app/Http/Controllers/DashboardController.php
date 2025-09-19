<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Conversation;
use App\Models\Conversion;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard principal
     */
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        return response()->json([
            'stats' => $this->getStats($companyId),
            'charts' => $this->getCharts($companyId),
            'recent_activity' => $this->getRecentActivity($companyId),
        ]);
    }

    /**
     * Estatísticas gerais
     */
    public function stats(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        return response()->json($this->getStats($companyId));
    }

    /**
     * Dados para gráficos
     */
    public function charts(Request $request)
    {
        $companyId = $request->user()->company_id;
        $period = $request->get('period', '30'); // dias
        
        return response()->json($this->getCharts($companyId, (int) $period));
    }

    /**
     * Obter estatísticas principais
     */
    private function getStats(int $companyId): array
    {
        $now = now();
        $thisMonth = $now->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();

        // Conversas
        $totalConversations = Conversation::forCompany($companyId)->count();
        $conversationsThisMonth = Conversation::forCompany($companyId)
            ->where('created_at', '>=', $thisMonth)
            ->count();
        $conversationsLastMonth = Conversation::forCompany($companyId)
            ->whereBetween('created_at', [$lastMonth, $thisMonth])
            ->count();

        // Leads
        $totalLeads = Lead::forCompany($companyId)->count();
        $trackedLeads = Lead::forCompany($companyId)->tracked()->count();
        $untrackedLeads = Lead::forCompany($companyId)->untracked()->count();

        // Conversões
        $totalConversions = Conversion::forCompany($companyId)->confirmed()->count();
        $totalRevenue = Conversion::forCompany($companyId)->confirmed()->sum('value');
        $conversionsThisMonth = Conversion::forCompany($companyId)
            ->confirmed()
            ->where('confirmed_at', '>=', $thisMonth)
            ->count();
        $revenueThisMonth = Conversion::forCompany($companyId)
            ->confirmed()
            ->where('confirmed_at', '>=', $thisMonth)
            ->sum('value');

        // Taxa de rastreamento
        $trackingRate = $totalLeads > 0 ? ($trackedLeads / $totalLeads) * 100 : 0;

        // Taxa de conversão
        $conversionRate = $totalLeads > 0 ? ($totalConversions / $totalLeads) * 100 : 0;

        // Crescimento mensal
        $conversationGrowth = $conversationsLastMonth > 0 
            ? (($conversationsThisMonth - $conversationsLastMonth) / $conversationsLastMonth) * 100 
            : 0;

        return [
            'conversations' => [
                'total' => $totalConversations,
                'this_month' => $conversationsThisMonth,
                'growth' => round($conversationGrowth, 1),
            ],
            'leads' => [
                'total' => $totalLeads,
                'tracked' => $trackedLeads,
                'untracked' => $untrackedLeads,
                'tracking_rate' => round($trackingRate, 1),
            ],
            'conversions' => [
                'total' => $totalConversions,
                'this_month' => $conversionsThisMonth,
                'conversion_rate' => round($conversionRate, 1),
            ],
            'revenue' => [
                'total' => $totalRevenue,
                'this_month' => $revenueThisMonth,
                'formatted_total' => 'R$ ' . number_format($totalRevenue, 2, ',', '.'),
                'formatted_this_month' => 'R$ ' . number_format($revenueThisMonth, 2, ',', '.'),
            ],
        ];
    }

    /**
     * Obter dados para gráficos
     */
    private function getCharts(int $companyId, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        // Conversas por origem ao longo do tempo
        $conversationsByOrigin = $this->getConversationsByOrigin($companyId, $startDate, $endDate);
        
        // Conversões por dia
        $conversionsByDay = $this->getConversionsByDay($companyId, $startDate, $endDate);
        
        // Distribuição por origem (donut chart)
        $originDistribution = $this->getOriginDistribution($companyId, $startDate, $endDate);

        // Performance por origem
        $originPerformance = $this->getOriginPerformance($companyId, $startDate, $endDate);

        return [
            'conversations_by_origin' => $conversationsByOrigin,
            'conversions_by_day' => $conversionsByDay,
            'origin_distribution' => $originDistribution,
            'origin_performance' => $originPerformance,
        ];
    }

    /**
     * Conversas por origem ao longo do tempo (gráfico de barras empilhadas)
     */
    private function getConversationsByOrigin(int $companyId, Carbon $startDate, Carbon $endDate): array
    {
        $data = DB::table('conversations')
            ->join('leads', 'conversations.lead_id', '=', 'leads.id')
            ->where('conversations.company_id', $companyId)
            ->whereBetween('conversations.created_at', [$startDate, $endDate])
            ->select([
                DB::raw('DATE(conversations.created_at) as date'),
                'leads.origin',
                DB::raw('COUNT(*) as count')
            ])
            ->groupBy('date', 'leads.origin')
            ->orderBy('date')
            ->get();

        // Organizar dados por data
        $result = [];
        $dates = [];
        
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dates[] = $dateStr;
            $result[$dateStr] = [
                'meta' => 0,
                'google' => 0,
                'outras' => 0,
                'nao_rastreada' => 0,
            ];
        }

        foreach ($data as $item) {
            if (isset($result[$item->date])) {
                $result[$item->date][$item->origin] = $item->count;
            }
        }

        return [
            'dates' => $dates,
            'data' => $result,
        ];
    }

    /**
     * Conversões por dia
     */
    private function getConversionsByDay(int $companyId, Carbon $startDate, Carbon $endDate): array
    {
        $data = Conversion::forCompany($companyId)
            ->confirmed()
            ->whereBetween('confirmed_at', [$startDate, $endDate])
            ->select([
                DB::raw('DATE(confirmed_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(value) as total_value'),
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dayData = $data->get($dateStr);
            
            $result[] = [
                'date' => $dateStr,
                'count' => $dayData->count ?? 0,
                'value' => $dayData->total_value ?? 0,
            ];
        }

        return $result;
    }

    /**
     * Distribuição por origem (donut chart)
     */
    private function getOriginDistribution(int $companyId, Carbon $startDate, Carbon $endDate): array
    {
        $data = Lead::forCompany($companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'origin',
                DB::raw('COUNT(*) as count')
            ])
            ->groupBy('origin')
            ->get();

        $total = $data->sum('count');
        
        $result = [];
        foreach ($data as $item) {
            $percentage = $total > 0 ? ($item->count / $total) * 100 : 0;
            
            $result[] = [
                'origin' => $item->origin,
                'name' => match($item->origin) {
                    'meta' => 'Meta Ads',
                    'google' => 'Google Ads',
                    'outras' => 'Outras Origens',
                    'nao_rastreada' => 'Não Rastreada',
                },
                'count' => $item->count,
                'percentage' => round($percentage, 1),
                'color' => match($item->origin) {
                    'meta' => '#1877f2',
                    'google' => '#4285f4',
                    'outras' => '#6b7280',
                    'nao_rastreada' => '#f97316',
                },
            ];
        }

        return $result;
    }

    /**
     * Performance por origem
     */
    private function getOriginPerformance(int $companyId, Carbon $startDate, Carbon $endDate): array
    {
        $data = DB::table('leads')
            ->leftJoin('conversions', function ($join) {
                $join->on('leads.id', '=', 'conversions.lead_id')
                     ->where('conversions.status', '=', 'confirmed');
            })
            ->where('leads.company_id', $companyId)
            ->whereBetween('leads.created_at', [$startDate, $endDate])
            ->select([
                'leads.origin',
                DB::raw('COUNT(DISTINCT leads.id) as lead_count'),
                DB::raw('COUNT(conversions.id) as conversion_count'),
                DB::raw('COALESCE(SUM(conversions.value), 0) as total_value'),
            ])
            ->groupBy('leads.origin')
            ->get();

        $result = [];
        foreach ($data as $item) {
            $conversionRate = $item->lead_count > 0 
                ? ($item->conversion_count / $item->lead_count) * 100 
                : 0;

            $result[] = [
                'origin' => $item->origin,
                'name' => match($item->origin) {
                    'meta' => 'Meta Ads',
                    'google' => 'Google Ads', 
                    'outras' => 'Outras Origens',
                    'nao_rastreada' => 'Não Rastreada',
                },
                'leads' => $item->lead_count,
                'conversions' => $item->conversion_count,
                'conversion_rate' => round($conversionRate, 1),
                'total_value' => $item->total_value,
                'formatted_value' => 'R$ ' . number_format($item->total_value, 2, ',', '.'),
            ];
        }

        return $result;
    }

    /**
     * Atividade recente
     */
    private function getRecentActivity(int $companyId): array
    {
        // Conversas recentes
        $recentConversations = Conversation::forCompany($companyId)
            ->with(['lead', 'assignedAgent'])
            ->orderBy('last_activity_at', 'desc')
            ->limit(5)
            ->get();

        // Conversões recentes
        $recentConversions = Conversion::forCompany($companyId)
            ->with(['lead', 'conversation', 'confirmedBy'])
            ->confirmed()
            ->orderBy('confirmed_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'conversations' => $recentConversations,
            'conversions' => $recentConversions,
        ];
    }
}
