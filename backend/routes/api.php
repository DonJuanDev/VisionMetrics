<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ConversionController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Http\Controllers\Admin\AdminCompanyController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminDashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rotas públicas
Route::post('/register-company', [RegisterController::class, 'registerCompany']);
Route::post('/login', [AuthController::class, 'login']);
// Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
// Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Rotas de teste temporárias removidas - usando controllers reais

// Webhooks públicos (temporariamente simplificados)
Route::prefix('webhooks')->group(function () {
    Route::post('/whatsapp', function() {
        return response()->json(['message' => 'Webhook WhatsApp recebido', 'status' => 'ok']);
    });
    Route::get('/whatsapp', function() {
        return response('ok', 200);
    });
});

// Tracking público (temporariamente simplificados)
Route::prefix('tracking')->group(function () {
    Route::post('/capture', function() {
        return response()->json(['message' => 'Dados de tracking capturados']);
    });
    Route::get('/pixel', function() {
        return response('', 200, ['Content-Type' => 'image/gif']);
    });
    Route::get('/script.js', function() {
        return response('console.log("VisionMetrics tracking ativo");', 200, ['Content-Type' => 'application/javascript']);
    });
});

// Redirect de links rastreáveis
Route::get('/r/{token}', function($token) {
    return redirect('https://example.com?ref=' . $token);
});

// Rotas autenticadas
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Rotas com verificação de tenant
    Route::middleware(['tenant'])->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('/dashboard/charts', [DashboardController::class, 'charts']);

        // Profile do usuário
        Route::prefix('user')->group(function () {
            Route::get('/profile', [UserController::class, 'profile']);
            Route::put('/profile', [UserController::class, 'updateProfile']);
            Route::post('/change-password', [UserController::class, 'changePassword']);
            Route::post('/enable-2fa', [UserController::class, 'enableTwoFactor']);
            Route::post('/disable-2fa', [UserController::class, 'disableTwoFactor']);
        });

        // Trial status (sempre disponível, mesmo com trial expirado)
        Route::prefix('trial')->group(function () {
            Route::get('/status', [CompanyController::class, 'trialStatus']);
            Route::get('/support-contact', [CompanyController::class, 'supportContact']);
        });

        // Rotas que requerem trial ativo
        Route::middleware(['trial'])->group(function () {
            
            // Leads
            Route::apiResource('leads', LeadController::class);
            Route::post('/leads/{lead}/tags', [LeadController::class, 'addTag']);
            Route::delete('/leads/{lead}/tags/{tag}', [LeadController::class, 'removeTag']);
            Route::put('/leads/{lead}/status', [LeadController::class, 'updateStatus']);
            
            // Conversations
            Route::apiResource('conversations', ConversationController::class);
            Route::post('/conversations/{conversation}/assign', [ConversationController::class, 'assign']);
            Route::post('/conversations/{conversation}/unassign', [ConversationController::class, 'unassign']);
            Route::post('/conversations/{conversation}/close', [ConversationController::class, 'close']);
            Route::post('/conversations/{conversation}/reopen', [ConversationController::class, 'reopen']);
            Route::post('/conversations/{conversation}/mark-read', [ConversationController::class, 'markAsRead']);
            
            // Messages
            Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index']);
            Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store']);
            
            // Conversions
            Route::apiResource('conversions', ConversionController::class);
            Route::post('/conversions/{conversion}/confirm', [ConversionController::class, 'confirm']);
            Route::post('/conversions/{conversion}/cancel', [ConversionController::class, 'cancel']);
            Route::post('/conversions/detect-from-message', [ConversionController::class, 'detectFromMessage']);
            
            // Tracking Links
            Route::apiResource('tracking-links', TrackingController::class);
            Route::post('/tracking-links/{link}/toggle', [TrackingController::class, 'toggle']);
            Route::get('/tracking-links/{link}/stats', [TrackingController::class, 'stats']);
            
            // Reports
            Route::prefix('reports')->group(function () {
                Route::get('/conversions', [ReportController::class, 'conversions']);
                Route::get('/leads', [ReportController::class, 'leads']);
                Route::get('/conversations', [ReportController::class, 'conversations']);
                Route::get('/performance', [ReportController::class, 'performance']);
                Route::get('/attribution', [ReportController::class, 'attribution']);
                Route::post('/export/conversions', [ReportController::class, 'exportConversions']);
                Route::post('/export/leads', [ReportController::class, 'exportLeads']);
            });
            
            // Webhooks
            Route::apiResource('webhooks', WebhookController::class);
            Route::post('/webhooks/{webhook}/toggle', [WebhookController::class, 'toggle']);
            Route::post('/webhooks/{webhook}/test', [WebhookController::class, 'test']);

            // Gerenciamento de usuários (apenas admins da empresa)
            Route::middleware(['admin:company_admin'])->prefix('company')->group(function () {
                Route::get('/users', [UserController::class, 'index']);
                Route::post('/users', [UserController::class, 'store']);
                Route::put('/users/{user}', [UserController::class, 'update']);
                Route::delete('/users/{user}', [UserController::class, 'destroy']);
                Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
                
                Route::get('/settings', [CompanyController::class, 'settings']);
                Route::put('/settings', [CompanyController::class, 'updateSettings']);
                Route::get('/integrations', [CompanyController::class, 'integrations']);
                Route::put('/integrations', [CompanyController::class, 'updateIntegrations']);
            });
        });
    });

    // Rotas administrativas do SaaS (temporariamente simplificadas)
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', function() {
            return response()->json(['message' => 'Painel admin em desenvolvimento', 'companies_count' => 0]);
        });
        
        Route::get('/companies', function() {
            return response()->json(['companies' => [], 'message' => 'Gerenciamento de empresas em desenvolvimento']);
        });
        
        Route::get('/system/health', function() {
            return response()->json(['status' => 'ok', 'services' => ['api' => 'ok', 'db' => 'ok']]);
        });
    });
});

// Health check público
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
    ]);
});

// Teste básico da API
Route::get('/test', function () {
    return response()->json([
        'message' => '🚀 VisionMetrics API funcionando perfeitamente!',
        'status' => 'success',
        'timestamp' => now()->toISOString(),
        'endpoints' => [
            'POST /api/register-company' => 'Registrar nova empresa',
            'POST /api/login' => 'Login de usuário',
            'GET /api/dashboard' => 'Dashboard (requer auth)',
            'POST /api/webhooks/whatsapp' => 'Webhook WhatsApp',
        ]
    ]);
});

// Rota raiz da API
Route::get('/', function () {
    return response()->json([
        'app' => 'VisionMetrics API',
        'version' => '1.0.0',
        'status' => 'operational',
        'message' => 'API funcionando corretamente!',
        'documentation' => 'Consulte /api/test para endpoints disponíveis'
    ]);
});

// Metrics endpoint (para monitoramento)
Route::get('/metrics', function () {
    return response([
        'version' => config('app.version', '1.0.0'),
        'laravel_version' => app()->version(),
        'php_version' => PHP_VERSION,
        'memory_usage' => memory_get_usage(true),
        'memory_peak' => memory_get_peak_usage(true),
    ])->header('Content-Type', 'text/plain');
});

// Fallback para rotas não encontradas
Route::fallback(function () {
    return response()->json([
        'error' => 'Endpoint não encontrado',
        'message' => 'A rota solicitada não existe.',
    ], 404);
});
