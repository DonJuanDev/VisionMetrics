<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\User;
use App\Models\Lead;
use App\Models\Conversation;
use App\Models\Conversion;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Dashboard principal do admin
     */
    public function index(Request $request)
    {
        return response()->json([
            'stats' => $this->getGlobalStats(),
            'charts' => $this->getGlobalCharts(),
            'recent_companies' => $this->getRecentCompanies(),
            'system_health' => $this->getSystemHealth(),
        ]);
    }

    /**
     * Estatísticas globais
     */
    public function stats(Request $request)
    {
        return response()->json($this->getGlobalStats());
    }

    /**
     * Logs de auditoria globais
     */
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with(['user', 'company'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->event) {
            $query->where('event', $request->event);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);

        return response()->json($logs);
    }

    /**
     * Saúde do sistema
     */
    public function systemHealth()
    {
        return response()->json($this->getSystemHealth());
    }

    /**
     * Métricas do sistema
     */
    public function metrics()
    {
        return response()->json([
            'database' => $this->getDatabaseMetrics(),
            'cache' => $this->getCacheMetrics(),
            'queue' => $this->getQueueMetrics(),
            'storage' => $this->getStorageMetrics(),
        ]);
    }

    /**
     * Obter estatísticas globais
     */
    private function getGlobalStats(): array
    {
        $now = now();
        $thisMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();

        // Empresas
        $totalCompanies = Company::count();
        $activeCompanies = Company::where('is_active', true)->count();
        $companiesThisMonth = Company::where('created_at', '>=', $thisMonth)->count();
        $trialExpiredCompanies = Company::where('trial_expires_at', '<', $now)->count();

        // Usuários
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $usersThisMonth = User::where('created_at', '>=', $thisMonth)->count();

        // Leads e Conversas
        $totalLeads = Lead::count();
        $totalConversations = Conversation::count();
        $leadsThisMonth = Lead::where('created_at', '>=', $thisMonth)->count();

        // Conversões
        $totalConversions = Conversion::where('status', 'confirmed')->count();
        $totalRevenue = Conversion::where('status', 'confirmed')->sum('value');
        $conversionsThisMonth = Conversion::where('status', 'confirmed')
            ->where('confirmed_at', '>=', $thisMonth)
            ->count();
        $revenueThisMonth = Conversion::where('status', 'confirmed')
            ->where('confirmed_at', '>=', $thisMonth)
            ->sum('value');

        return [
            'companies' => [
                'total' => $totalCompanies,
                'active' => $activeCompanies,
                'this_month' => $companiesThisMonth,
                'trial_expired' => $trialExpiredCompanies,
            ],
            'users' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'this_month' => $usersThisMonth,
            ],
            'leads' => [
                'total' => $totalLeads,
                'this_month' => $leadsThisMonth,
            ],
            'conversations' => [
                'total' => $totalConversations,
            ],
            'conversions' => [
                'total' => $totalConversions,
                'this_month' => $conversionsThisMonth,
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
     * Obter gráficos globais
     */
    private function getGlobalCharts(): array
    {
        $startDate = now()->subDays(30);
        $endDate = now();

        // Crescimento de empresas
        $companyGrowth = $this->getCompanyGrowthChart($startDate, $endDate);
        
        // Revenue por dia
        $dailyRevenue = $this->getDailyRevenueChart($startDate, $endDate);
        
        // Distribuição por status de trial
        $trialDistribution = $this->getTrialDistributionChart();

        return [
            'company_growth' => $companyGrowth,
            'daily_revenue' => $dailyRevenue,
            'trial_distribution' => $trialDistribution,
        ];
    }

    /**
     * Empresas recentes
     */
    private function getRecentCompanies(): array
    {
        return Company::with(['users' => function($q) {
            $q->where('role', 'company_admin');
        }])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get()
        ->toArray();
    }

    /**
     * Saúde do sistema
     */
    private function getSystemHealth(): array
    {
        $health = [
            'status' => 'healthy',
            'checks' => [],
        ];

        // Database check
        try {
            DB::connection()->getPdo();
            $health['checks']['database'] = [
                'status' => 'ok',
                'message' => 'Database connection successful',
            ];
        } catch (\Exception $e) {
            $health['status'] = 'unhealthy';
            $health['checks']['database'] = [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }

        // Redis check
        try {
            $redis = \Illuminate\Support\Facades\Redis::connection();
            $redis->ping();
            $health['checks']['redis'] = [
                'status' => 'ok',
                'message' => 'Redis connection successful',
            ];
        } catch (\Exception $e) {
            $health['checks']['redis'] = [
                'status' => 'warning',
                'message' => 'Redis connection failed: ' . $e->getMessage(),
            ];
        }

        // Storage check
        try {
            $storageWritable = is_writable(storage_path());
            $health['checks']['storage'] = [
                'status' => $storageWritable ? 'ok' : 'error',
                'message' => $storageWritable ? 'Storage is writable' : 'Storage is not writable',
            ];
        } catch (\Exception $e) {
            $health['checks']['storage'] = [
                'status' => 'error',
                'message' => 'Storage check failed: ' . $e->getMessage(),
            ];
        }

        return $health;
    }

    private function getCompanyGrowthChart($startDate, $endDate): array
    {
        $data = Company::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $result[] = [
                'date' => $dateStr,
                'companies' => $data->get($dateStr)->count ?? 0,
            ];
        }

        return $result;
    }

    private function getDailyRevenueChart($startDate, $endDate): array
    {
        $data = Conversion::where('status', 'confirmed')
            ->whereBetween('confirmed_at', [$startDate, $endDate])
            ->select([
                DB::raw('DATE(confirmed_at) as date'),
                DB::raw('SUM(value) as revenue')
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $result[] = [
                'date' => $dateStr,
                'revenue' => $data->get($dateStr)->revenue ?? 0,
            ];
        }

        return $result;
    }

    private function getTrialDistributionChart(): array
    {
        $now = now();
        
        $active = Company::where('trial_expires_at', '>', $now)->count();
        $expired = Company::where('trial_expires_at', '<=', $now)->count();
        $noTrial = Company::whereNull('trial_expires_at')->count();

        return [
            ['label' => 'Trial Ativo', 'value' => $active, 'color' => '#10b981'],
            ['label' => 'Trial Expirado', 'value' => $expired, 'color' => '#ef4444'],
            ['label' => 'Sem Trial', 'value' => $noTrial, 'color' => '#6b7280'],
        ];
    }

    private function getDatabaseMetrics(): array
    {
        return [
            'tables_count' => DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()")[0]->count,
            'total_size' => DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS size FROM information_schema.tables WHERE table_schema = DATABASE()")[0]->size . ' MB',
        ];
    }

    private function getCacheMetrics(): array
    {
        try {
            $redis = \Illuminate\Support\Facades\Redis::connection();
            $info = $redis->info();
            
            return [
                'connected_clients' => $info['connected_clients'] ?? 0,
                'used_memory_human' => $info['used_memory_human'] ?? '0B',
                'total_commands_processed' => $info['total_commands_processed'] ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Redis not available',
            ];
        }
    }

    private function getQueueMetrics(): array
    {
        return [
            'pending_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
        ];
    }

    private function getStorageMetrics(): array
    {
        $storagePath = storage_path();
        $freeBytes = disk_free_space($storagePath);
        $totalBytes = disk_total_space($storagePath);
        
        return [
            'free_space' => $this->formatBytes($freeBytes),
            'total_space' => $this->formatBytes($totalBytes),
            'used_percentage' => round(($totalBytes - $freeBytes) / $totalBytes * 100, 2),
        ];
    }

    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}
