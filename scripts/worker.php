#!/usr/bin/env php
<?php
/**
 * VisionMetrics - Queue Worker for CRON
 * 
 * Processes pending queue jobs and sends events to analytics adapters (GA4, Meta)
 * 
 * CRON Configuration (run every 1-5 minutes):
 * *//*5 * * * * cd /path/to/project && php scripts/worker.php >> logs/worker.log 2>&1
 * 
 * OR every minute for faster processing:
 * * * * * * cd /path/to/project && php scripts/worker.php >> logs/worker.log 2>&1
 * 
 * Features:
 * - Processes pending jobs from queue_jobs table
 * - Exponential backoff on failures
 * - Max retry attempts
 * - Support for multiple job types: click, conversion, whatsapp_message
 * - Logs to logs/worker.log
 */

// Ensure CLI mode
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line.');
}

// Bootstrap application
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../src/adapters/GA4Adapter.php';
require_once __DIR__ . '/../src/adapters/MetaAdapter.php';

use VisionMetrics\Adapters\GA4Adapter;
use VisionMetrics\Adapters\MetaAdapter;

// ═══════════════════════════════════════════════════════════
// WORKER CONFIGURATION
// ═══════════════════════════════════════════════════════════
$maxJobsPerRun = 50; // Process max 50 jobs per execution
$lockTimeout = 300; // 5 minutes lock timeout

// ═══════════════════════════════════════════════════════════
// LOGGING HELPER
// ═══════════════════════════════════════════════════════════
function workerLog($level, $message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logEntry = "[{$timestamp}] [{$level}] {$message} {$contextStr}\n";
    
    $logFile = __DIR__ . '/../logs/worker.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    // Also output to console
    echo $logEntry;
}

// ═══════════════════════════════════════════════════════════
// START WORKER RUN
// ═══════════════════════════════════════════════════════════
workerLog('INFO', '=== Worker started ===');

try {
    $db = getDB();
    
    // ═══════════════════════════════════════════════════════════
    // FETCH PENDING JOBS
    // ═══════════════════════════════════════════════════════════
    $stmt = $db->prepare("
        SELECT * FROM queue_jobs
        WHERE status = 'pending'
        AND (next_run_at IS NULL OR next_run_at <= NOW())
        AND attempts < max_attempts
        ORDER BY created_at ASC
        LIMIT ?
    ");
    $stmt->execute([$maxJobsPerRun]);
    $jobs = $stmt->fetchAll();
    
    $jobCount = count($jobs);
    workerLog('INFO', "Found {$jobCount} pending jobs to process");
    
    if ($jobCount === 0) {
        workerLog('INFO', 'No jobs to process. Exiting.');
        exit(0);
    }
    
    // ═══════════════════════════════════════════════════════════
    // PROCESS EACH JOB
    // ═══════════════════════════════════════════════════════════
    $successCount = 0;
    $failureCount = 0;
    
    foreach ($jobs as $job) {
        $jobId = $job['id'];
        $jobType = $job['type'];
        $workspaceId = $job['workspace_id'];
        $attempts = (int)$job['attempts'];
        $payload = json_decode($job['payload'], true);
        
        workerLog('INFO', "Processing job #{$jobId}", [
            'type' => $jobType,
            'workspace_id' => $workspaceId,
            'attempts' => $attempts
        ]);
        
        // Mark as processing
        $stmt = $db->prepare("
            UPDATE queue_jobs 
            SET status = 'processing', attempts = attempts + 1
            WHERE id = ?
        ");
        $stmt->execute([$jobId]);
        
        try {
            // Process based on job type
            $result = processJob($jobType, $payload, $workspaceId, $db);
            
            if ($result['success']) {
                // Mark as completed
                $stmt = $db->prepare("
                    UPDATE queue_jobs
                    SET status = 'completed', 
                        response = ?,
                        processed_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    json_encode($result),
                    $jobId
                ]);
                
                workerLog('INFO', "Job #{$jobId} completed successfully", $result);
                $successCount++;
            } else {
                throw new Exception($result['error'] ?? 'Unknown error');
            }
            
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $newAttempts = $attempts + 1;
            
            workerLog('ERROR', "Job #{$jobId} failed: {$errorMessage}", [
                'attempts' => $newAttempts,
                'max_attempts' => $job['max_attempts']
            ]);
            
            // Check if max attempts reached
            if ($newAttempts >= $job['max_attempts']) {
                // Mark as failed permanently
                $stmt = $db->prepare("
                    UPDATE queue_jobs
                    SET status = 'failed',
                        error_message = ?
                    WHERE id = ?
                ");
                $stmt->execute([$errorMessage, $jobId]);
                
                workerLog('ERROR', "Job #{$jobId} permanently failed after {$newAttempts} attempts");
            } else {
                // Calculate exponential backoff: 2^attempts * 60 seconds
                $backoffSeconds = pow(2, $newAttempts) * 60;
                
                $stmt = $db->prepare("
                    UPDATE queue_jobs
                    SET status = 'pending',
                        error_message = ?,
                        next_run_at = DATE_ADD(NOW(), INTERVAL ? SECOND)
                    WHERE id = ?
                ");
                $stmt->execute([$errorMessage, $backoffSeconds, $jobId]);
                
                workerLog('INFO', "Job #{$jobId} rescheduled in {$backoffSeconds} seconds");
            }
            
            $failureCount++;
        }
    }
    
    // ═══════════════════════════════════════════════════════════
    // WORKER RUN COMPLETE
    // ═══════════════════════════════════════════════════════════
    workerLog('INFO', '=== Worker finished ===', [
        'processed' => $jobCount,
        'success' => $successCount,
        'failures' => $failureCount
    ]);
    
} catch (Exception $e) {
    workerLog('CRITICAL', 'Worker crashed: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit(1);
}

exit(0);

// ═══════════════════════════════════════════════════════════
// JOB PROCESSING FUNCTION
// ═══════════════════════════════════════════════════════════
function processJob($type, $payload, $workspaceId, $db) {
    switch ($type) {
        case 'click':
            return processClickJob($payload, $workspaceId);
            
        case 'conversion':
            return processConversionJob($payload, $workspaceId);
            
        case 'whatsapp_message':
            return processWhatsAppMessageJob($payload, $workspaceId);
            
        default:
            throw new Exception("Unknown job type: {$type}");
    }
}

// ═══════════════════════════════════════════════════════════
// CLICK JOB PROCESSOR
// ═══════════════════════════════════════════════════════════
function processClickJob($payload, $workspaceId) {
    $results = ['ga4' => null, 'meta' => null];
    $errors = [];
    
    // Extract data from payload
    $cookieToken = $payload['cookie_token'] ?? null;
    $utm = $payload['utm'] ?? [];
    $url = $payload['url'] ?? '';
    $ip = $payload['ip'] ?? '';
    $userAgent = $payload['user_agent'] ?? '';
    
    // ─────────────────────────────────────────────────────────
    // Send to GA4
    // ─────────────────────────────────────────────────────────
    try {
        $ga4 = new GA4Adapter($workspaceId);
        
        $eventParams = [
            'page_location' => $url,
            'page_referrer' => $payload['referrer'] ?? '',
        ];
        
        // Add UTM parameters if available
        if (!empty($utm['source'])) $eventParams['campaign_source'] = $utm['source'];
        if (!empty($utm['medium'])) $eventParams['campaign_medium'] = $utm['medium'];
        if (!empty($utm['campaign'])) $eventParams['campaign_name'] = $utm['campaign'];
        if (!empty($utm['term'])) $eventParams['campaign_term'] = $utm['term'];
        if (!empty($utm['content'])) $eventParams['campaign_content'] = $utm['content'];
        
        $result = $ga4->sendEvent('page_view', $cookieToken, $eventParams);
        $results['ga4'] = $result;
        
        if (!$result['success']) {
            $errors[] = 'GA4: ' . ($result['error'] ?? 'Unknown error');
        }
    } catch (Exception $e) {
        $errors[] = 'GA4: ' . $e->getMessage();
        $results['ga4'] = ['success' => false, 'error' => $e->getMessage()];
    }
    
    // ─────────────────────────────────────────────────────────
    // Send to Meta
    // ─────────────────────────────────────────────────────────
    try {
        $meta = new MetaAdapter($workspaceId);
        
        $userData = [
            'ip' => $ip,
            'user_agent' => $userAgent,
            'page_url' => $url
        ];
        
        $customData = [
            'content_name' => 'Link Click',
            'content_category' => $utm['campaign'] ?? 'shortener'
        ];
        
        // Use click_id as event_id for deduplication
        $eventId = 'click_' . ($payload['click_id'] ?? uniqid());
        
        $result = $meta->sendConversion('PageView', $userData, $customData, $eventId);
        $results['meta'] = $result;
        
        if (!$result['success']) {
            $errors[] = 'Meta: ' . ($result['error'] ?? 'Unknown error');
        }
    } catch (Exception $e) {
        $errors[] = 'Meta: ' . $e->getMessage();
        $results['meta'] = ['success' => false, 'error' => $e->getMessage()];
    }
    
    // ─────────────────────────────────────────────────────────
    // Return result
    // ─────────────────────────────────────────────────────────
    if (!empty($errors)) {
        return [
            'success' => false,
            'error' => implode('; ', $errors),
            'adapters' => $results
        ];
    }
    
    return [
        'success' => true,
        'adapters' => $results
    ];
}

// ═══════════════════════════════════════════════════════════
// CONVERSION JOB PROCESSOR
// ═══════════════════════════════════════════════════════════
function processConversionJob($payload, $workspaceId) {
    $results = ['ga4' => null, 'meta' => null];
    $errors = [];
    
    $cookieToken = $payload['cookie_token'] ?? $payload['client_id'] ?? null;
    $value = $payload['value'] ?? 0;
    $currency = $payload['currency'] ?? 'BRL';
    $conversionType = $payload['conversion_type'] ?? 'purchase';
    
    // ─────────────────────────────────────────────────────────
    // Send to GA4
    // ─────────────────────────────────────────────────────────
    try {
        $ga4 = new GA4Adapter($workspaceId);
        
        $eventParams = [
            'currency' => $currency,
            'value' => $value,
            'transaction_id' => $payload['transaction_id'] ?? uniqid('txn_')
        ];
        
        $result = $ga4->sendEvent('purchase', $cookieToken, $eventParams);
        $results['ga4'] = $result;
        
        if (!$result['success']) {
            $errors[] = 'GA4: ' . ($result['error'] ?? 'Unknown error');
        }
    } catch (Exception $e) {
        $errors[] = 'GA4: ' . $e->getMessage();
        $results['ga4'] = ['success' => false, 'error' => $e->getMessage()];
    }
    
    // ─────────────────────────────────────────────────────────
    // Send to Meta
    // ─────────────────────────────────────────────────────────
    try {
        $meta = new MetaAdapter($workspaceId);
        
        $userData = [
            'email' => $payload['email'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'ip' => $payload['ip'] ?? null,
            'user_agent' => $payload['user_agent'] ?? null,
            'page_url' => $payload['page_url'] ?? ''
        ];
        
        $customData = [
            'value' => $value,
            'currency' => $currency,
            'content_name' => $conversionType
        ];
        
        $eventId = 'conv_' . ($payload['conversion_id'] ?? uniqid());
        
        $result = $meta->sendConversion('Purchase', $userData, $customData, $eventId);
        $results['meta'] = $result;
        
        if (!$result['success']) {
            $errors[] = 'Meta: ' . ($result['error'] ?? 'Unknown error');
        }
    } catch (Exception $e) {
        $errors[] = 'Meta: ' . $e->getMessage();
        $results['meta'] = ['success' => false, 'error' => $e->getMessage()];
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'error' => implode('; ', $errors),
            'adapters' => $results
        ];
    }
    
    return [
        'success' => true,
        'adapters' => $results
    ];
}

// ═══════════════════════════════════════════════════════════
// WHATSAPP MESSAGE JOB PROCESSOR
// ═══════════════════════════════════════════════════════════
function processWhatsAppMessageJob($payload, $workspaceId) {
    $results = ['ga4' => null, 'meta' => null];
    $errors = [];
    
    $cookieToken = $payload['cookie_token'] ?? null;
    $phone = $payload['phone'] ?? null;
    
    // ─────────────────────────────────────────────────────────
    // Send to GA4
    // ─────────────────────────────────────────────────────────
    try {
        $ga4 = new GA4Adapter($workspaceId);
        
        $eventParams = [
            'engagement_type' => 'whatsapp_message',
            'method' => 'whatsapp'
        ];
        
        $result = $ga4->sendEvent('generate_lead', $cookieToken, $eventParams);
        $results['ga4'] = $result;
        
        if (!$result['success']) {
            $errors[] = 'GA4: ' . ($result['error'] ?? 'Unknown error');
        }
    } catch (Exception $e) {
        $errors[] = 'GA4: ' . $e->getMessage();
        $results['ga4'] = ['success' => false, 'error' => $e->getMessage()];
    }
    
    // ─────────────────────────────────────────────────────────
    // Send to Meta
    // ─────────────────────────────────────────────────────────
    try {
        $meta = new MetaAdapter($workspaceId);
        
        $userData = [
            'phone' => $phone,
            'page_url' => 'whatsapp://message'
        ];
        
        $customData = [
            'content_name' => 'WhatsApp Lead',
            'content_category' => 'messaging'
        ];
        
        $eventId = 'wa_' . ($payload['message_id'] ?? uniqid());
        
        $result = $meta->sendConversion('Lead', $userData, $customData, $eventId);
        $results['meta'] = $result;
        
        if (!$result['success']) {
            $errors[] = 'Meta: ' . ($result['error'] ?? 'Unknown error');
        }
    } catch (Exception $e) {
        $errors[] = 'Meta: ' . $e->getMessage();
        $results['meta'] = ['success' => false, 'error' => $e->getMessage()];
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'error' => implode('; ', $errors),
            'adapters' => $results
        ];
    }
    
    return [
        'success' => true,
        'adapters' => $results
    ];
}



