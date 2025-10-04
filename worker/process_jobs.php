#!/usr/bin/env php
<?php
/**
 * VisionMetrics - Worker Queue Processor
 * Processa jobs de integração em background
 * 
 * USO:
 * php worker/process_jobs.php
 * 
 * OU via Docker:
 * docker-compose exec worker php /var/www/html/worker/process_jobs.php
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/adapters/MetaAdapter.php';
require_once __DIR__ . '/../src/adapters/GA4Adapter.php';
require_once __DIR__ . '/../src/adapters/WhatsAppAdapter.php';
require_once __DIR__ . '/../src/adapters/TikTokAdapter.php';

use VisionMetrics\Adapters\MetaAdapter;
use VisionMetrics\Adapters\GA4Adapter;
use VisionMetrics\Adapters\WhatsAppAdapter;
use VisionMetrics\Adapters\TikTokAdapter;

echo "🚀 VisionMetrics Worker Started\n";
echo "Mode: " . ADAPTER_MODE . "\n";
echo "===================================\n\n";

$db = getDB();
$processedCount = 0;

while (true) {
    try {
        // Buscar jobs pendentes (incluindo queued)
        $stmt = $db->prepare("
            SELECT * FROM jobs_log 
            WHERE status IN ('pending', 'queued') 
            AND (next_run_at IS NULL OR next_run_at <= NOW())
            ORDER BY created_at ASC
            LIMIT 10
        ");
        $stmt->execute();
        $jobs = $stmt->fetchAll();
        
        // Processar job de agregação de métricas (uma vez por dia)
        if (empty($jobs) || rand(1, 100) <= 5) { // 5% chance de processar métricas
            processMetricsAggregation($db);
        }
        
        if (empty($jobs)) {
            echo "[" . date('Y-m-d H:i:s') . "] No pending jobs. Waiting 10s...\n";
            sleep(10);
            continue;
        }
        
        echo "[" . date('Y-m-d H:i:s') . "] Found " . count($jobs) . " jobs to process\n";
        
        foreach ($jobs as $job) {
            processJob($job, $db);
            $processedCount++;
        }
        
        echo "Processed: $processedCount total jobs\n\n";
        
        // Small delay between batches
        sleep(2);
        
    } catch (Exception $e) {
        logMessage('ERROR', 'Worker error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        echo "ERROR: " . $e->getMessage() . "\n";
        sleep(5);
    }
}

function processJob($job, $db) {
    $jobId = $job['id'];
    $adapter = $job['adapter'];
    $payload = json_decode($job['payload'], true);
    
    echo "  Processing job #{$jobId} - {$adapter}\n";
    
    // Marcar como processando
    $stmt = $db->prepare("
        UPDATE jobs_log 
        SET status = 'processing', tries = tries + 1 
        WHERE id = ?
    ");
    $stmt->execute([$jobId]);
    
    try {
        $result = null;
        
        // Processar por adapter
        switch ($adapter) {
            case 'meta':
                $metaAdapter = new MetaAdapter($job['workspace_id']);
                $result = $metaAdapter->sendConversion(
                    $payload['event_name'] ?? 'Lead',
                    $payload['user_data'] ?? [],
                    $payload['custom_data'] ?? [],
                    $payload['event_id'] ?? null
                );
                break;
                
            case 'ga4':
                $ga4Adapter = new GA4Adapter($job['workspace_id']);
                $result = $ga4Adapter->sendEvent(
                    $payload['event_name'] ?? 'page_view',
                    $payload['client_id'] ?? 'unknown',
                    $payload['params'] ?? []
                );
                break;
                
            case 'whatsapp':
                $whatsappAdapter = new WhatsAppAdapter();
                $result = $whatsappAdapter->sendMessage(
                    $payload['to'] ?? '',
                    $payload['message'] ?? '',
                    $payload['context'] ?? null
                );
                break;
                
            case 'tiktok':
                $tiktokAdapter = new TikTokAdapter($job['workspace_id']);
                $result = $tiktokAdapter->sendEvent(
                    $payload['event_name'] ?? 'PageView',
                    $payload['user_data'] ?? [],
                    $payload['params'] ?? []
                );
                break;
                
            default:
                throw new Exception("Unknown adapter: {$adapter}");
        }
        
        // Marcar como completo
        $stmt = $db->prepare("
            UPDATE jobs_log 
            SET status = 'completed',
                response = ?,
                processed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([json_encode($result), $jobId]);
        
        echo "  ✅ Job #{$jobId} completed\n";
        
    } catch (Exception $e) {
        $tries = $job['tries'] + 1;
        $maxTries = $job['max_tries'] ?? 5;
        
        if ($tries >= $maxTries) {
            // Failed permanently
            $stmt = $db->prepare("
                UPDATE jobs_log 
                SET status = 'failed',
                    error = ?,
                    processed_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$e->getMessage(), $jobId]);
            
            echo "  ❌ Job #{$jobId} failed permanently: " . $e->getMessage() . "\n";
            
        } else {
            // Retry with exponential backoff
            $delay = 5 * pow(2, $tries); // 5, 10, 20, 40, 80 seconds
            $nextRun = date('Y-m-d H:i:s', time() + $delay);
            
            $stmt = $db->prepare("
                UPDATE jobs_log 
                SET status = 'pending',
                    error = ?,
                    next_run_at = ?
                WHERE id = ?
            ");
            $stmt->execute([$e->getMessage(), $nextRun, $jobId]);
            
            echo "  ⚠️  Job #{$jobId} failed, retry in {$delay}s: " . $e->getMessage() . "\n";
        }
    }
}

function processMetricsAggregation($db) {
    echo "[" . date('Y-m-d H:i:s') . "] Processing metrics aggregation\n";
    
    try {
        // Get all workspaces
        $stmt = $db->prepare("SELECT id FROM workspaces WHERE status = 'active'");
        $stmt->execute();
        $workspaces = $stmt->fetchAll();
        
        foreach ($workspaces as $workspace) {
            $workspaceId = $workspace['id'];
            $today = date('Y-m-d');
            
            // Check if already processed today
            $stmt = $db->prepare("
                SELECT COUNT(*) as count FROM metrics_daily 
                WHERE workspace_id = ? AND date = ? AND metric_type = 'daily_summary'
            ");
            $stmt->execute([$workspaceId, $today]);
            $alreadyProcessed = $stmt->fetch()['count'] > 0;
            
            if ($alreadyProcessed) continue;
            
            // Aggregate daily metrics
            aggregateDailyMetrics($db, $workspaceId, $today);
        }
        
    } catch (Exception $e) {
        echo "ERROR in metrics aggregation: " . $e->getMessage() . "\n";
    }
}

function aggregateDailyMetrics($db, $workspaceId, $date) {
    // Get conversations by channel
    $stmt = $db->prepare("
        SELECT 
            CASE 
                WHEN utm_source LIKE '%meta%' OR utm_source LIKE '%facebook%' OR fbclid IS NOT NULL THEN 'meta_ads'
                WHEN utm_source LIKE '%google%' OR gclid IS NOT NULL THEN 'google_ads'
                WHEN utm_source LIKE '%tiktok%' OR ttclid IS NOT NULL THEN 'tiktok_ads'
                WHEN utm_source IS NOT NULL THEN utm_source
                ELSE 'direct'
            END as channel,
            utm_campaign as campaign,
            COUNT(*) as conversations,
            SUM(CASE WHEN is_sale = 1 THEN 1 ELSE 0 END) as sales
        FROM conversations 
        WHERE workspace_id = ? AND DATE(created_at) = ?
        GROUP BY channel, campaign
    ");
    $stmt->execute([$workspaceId, $date]);
    $conversations = $stmt->fetchAll();
    
    // Get events by channel
    $stmt = $db->prepare("
        SELECT 
            CASE 
                WHEN utm_source LIKE '%meta%' OR utm_source LIKE '%facebook%' OR fbclid IS NOT NULL THEN 'meta_ads'
                WHEN utm_source LIKE '%google%' OR gclid IS NOT NULL THEN 'google_ads'
                WHEN utm_source LIKE '%tiktok%' OR ttclid IS NOT NULL THEN 'tiktok_ads'
                WHEN utm_source IS NOT NULL THEN utm_source
                ELSE 'direct'
            END as channel,
            utm_campaign as campaign,
            COUNT(*) as events,
            COUNT(DISTINCT lead_id) as unique_leads
        FROM events 
        WHERE workspace_id = ? AND DATE(created_at) = ?
        GROUP BY channel, campaign
    ");
    $stmt->execute([$workspaceId, $date]);
    $events = $stmt->fetchAll();
    
    // Store metrics
    $metrics = [
        'conversations' => $conversations,
        'events' => $events
    ];
    
    foreach ($metrics as $type => $data) {
        foreach ($data as $row) {
            $channel = $row['channel'] ?? 'unknown';
            $campaign = $row['campaign'] ?? null;
            
            // Store each metric type
            if ($type === 'conversations') {
                storeMetric($db, $workspaceId, $date, 'conversations', $channel, $campaign, $row['conversations']);
                storeMetric($db, $workspaceId, $date, 'sales', $channel, $campaign, $row['sales']);
            } else {
                storeMetric($db, $workspaceId, $date, 'events', $channel, $campaign, $row['events']);
                storeMetric($db, $workspaceId, $date, 'unique_leads', $channel, $campaign, $row['unique_leads']);
            }
        }
    }
    
    // Calculate ROAS and CPA (simplified - would need actual ad spend data)
    calculateROASAndCPA($db, $workspaceId, $date);
    
    echo "  ✓ Aggregated metrics for workspace {$workspaceId} on {$date}\n";
}

function storeMetric($db, $workspaceId, $date, $metricType, $channel, $campaign, $value) {
    $stmt = $db->prepare("
        INSERT INTO metrics_daily (workspace_id, date, metric_type, channel, campaign, value)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()
    ");
    $stmt->execute([$workspaceId, $date, $metricType, $channel, $campaign, $value]);
}

function calculateROASAndCPA($db, $workspaceId, $date) {
    // Simplified ROAS calculation (would need actual ad spend data)
    $stmt = $db->prepare("
        SELECT 
            channel,
            campaign,
            SUM(CASE WHEN metric_type = 'sales' THEN value ELSE 0 END) as sales,
            SUM(CASE WHEN metric_type = 'conversations' THEN value ELSE 0 END) as conversations
        FROM metrics_daily 
        WHERE workspace_id = ? AND date = ? AND metric_type IN ('sales', 'conversations')
        GROUP BY channel, campaign
    ");
    $stmt->execute([$workspaceId, $date]);
    $data = $stmt->fetchAll();
    
    foreach ($data as $row) {
        $channel = $row['channel'];
        $campaign = $row['campaign'];
        $sales = $row['sales'];
        $conversations = $row['conversations'];
        
        // Simplified calculations (would need real ad spend data)
        $estimatedAdSpend = $conversations * 2.50; // $2.50 per conversation estimate
        $estimatedRevenue = $sales * 100; // $100 per sale estimate
        
        if ($estimatedAdSpend > 0) {
            $roas = $estimatedRevenue / $estimatedAdSpend;
            storeMetric($db, $workspaceId, $date, 'roas', $channel, $campaign, $roas);
        }
        
        if ($conversations > 0) {
            $cpa = $estimatedAdSpend / $conversations;
            storeMetric($db, $workspaceId, $date, 'cpa', $channel, $campaign, $cpa);
        }
    }
}