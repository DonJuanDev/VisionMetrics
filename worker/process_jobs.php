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
        // Buscar jobs pendentes
        $stmt = $db->prepare("
            SELECT * FROM jobs_log 
            WHERE status = 'pending' 
            AND (next_run_at IS NULL OR next_run_at <= NOW())
            ORDER BY created_at ASC
            LIMIT 10
        ");
        $stmt->execute();
        $jobs = $stmt->fetchAll();
        
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
                $metaAdapter = new MetaAdapter();
                $result = $metaAdapter->sendConversion(
                    $payload['event_name'] ?? 'Lead',
                    $payload['user_data'] ?? [],
                    $payload['custom_data'] ?? [],
                    $payload['event_id'] ?? null
                );
                break;
                
            case 'ga4':
                $ga4Adapter = new GA4Adapter();
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
                $tiktokAdapter = new TikTokAdapter();
                $result = $tiktokAdapter->sendEvent(
                    $payload['event'] ?? 'PageView',
                    $payload['user_data'] ?? [],
                    $payload['properties'] ?? []
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