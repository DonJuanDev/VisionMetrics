<?php
/**
 * Observability & Monitoring
 * Sentry integration and logging utilities
 */

class Observability {
    private static $sentryEnabled = false;
    private static $sentryDsn = null;
    
    public static function init() {
        self::$sentryDsn = getenv('SENTRY_DSN');
        self::$sentryEnabled = !empty(self::$sentryDsn);
        
        if (self::$sentryEnabled) {
            // Initialize Sentry (would require sentry/sentry package)
            // \Sentry\init(['dsn' => self::$sentryDsn]);
        }
    }
    
    public static function captureException($exception, $context = []) {
        if (self::$sentryEnabled) {
            // \Sentry\captureException($exception, $context);
        }
        
        // Fallback to file logging
        self::logError('Exception: ' . $exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'context' => $context
        ]);
    }
    
    public static function captureMessage($message, $level = 'info', $context = []) {
        if (self::$sentryEnabled) {
            // \Sentry\captureMessage($message, $level, $context);
        }
        
        // Fallback to file logging
        self::logMessage($level, $message, $context);
    }
    
    public static function addBreadcrumb($message, $category = 'default', $level = 'info', $data = []) {
        if (self::$sentryEnabled) {
            // \Sentry\addBreadcrumb([
            //     'message' => $message,
            //     'category' => $category,
            //     'level' => $level,
            //     'data' => $data
            // ]);
        }
    }
    
    public static function setUser($userId, $email = null, $username = null) {
        if (self::$sentryEnabled) {
            // \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($userId, $email, $username) {
            //     $scope->setUser([
            //         'id' => $userId,
            //         'email' => $email,
            //         'username' => $username
            //     ]);
            // });
        }
    }
    
    public static function setTag($key, $value) {
        if (self::$sentryEnabled) {
            // \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($key, $value) {
            //     $scope->setTag($key, $value);
            // });
        }
    }
    
    public static function setContext($key, $data) {
        if (self::$sentryEnabled) {
            // \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($key, $data) {
            //     $scope->setContext($key, $data);
            // });
        }
    }
    
    private static function logError($message, $context = []) {
        self::logMessage('ERROR', $message, $context);
    }
    
    private static function logMessage($level, $message, $context = []) {
        $logEntry = [
            'timestamp' => date('c'),
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true)
        ];
        
        $logLine = json_encode($logEntry) . "\n";
        
        // Write to log file
        $logFile = __DIR__ . '/../logs/app.log';
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // Rotate logs if needed
        self::rotateLogs($logFile);
    }
    
    private static function rotateLogs($logFile) {
        if (!file_exists($logFile)) return;
        
        $maxSize = 10 * 1024 * 1024; // 10MB
        if (filesize($logFile) > $maxSize) {
            $rotatedFile = $logFile . '.' . date('Y-m-d-H-i-s');
            rename($logFile, $rotatedFile);
            
            // Keep only last 5 rotated files
            $logDir = dirname($logFile);
            $files = glob($logDir . '/app.log.*');
            if (count($files) > 5) {
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                
                foreach (array_slice($files, 5) as $file) {
                    unlink($file);
                }
            }
        }
    }
    
    public static function getMetrics() {
        return [
            'sentry_enabled' => self::$sentryEnabled,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'memory_limit' => ini_get('memory_limit'),
            'uptime' => time() - $_SERVER['REQUEST_TIME_FLOAT'],
            'php_version' => PHP_VERSION,
            'timestamp' => date('c')
        ];
    }
}

// Auto-initialize
Observability::init();
