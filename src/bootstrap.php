<?php
/**
 * VisionMetrics Bootstrap
 * Carrega configurações, sessão, headers de segurança
 */

// Load Composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load environment variables
if (class_exists('Dotenv\Dotenv')) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->safeLoad();
    } catch (Exception $e) {
        // Fallback: load .env manually for Hostinger
        if (file_exists(__DIR__ . '/../.env')) {
            $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0 || empty(trim($line))) continue;
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $name = trim($parts[0]);
                    $value = trim($parts[1]);
                    if (!array_key_exists($name, $_ENV)) {
                        putenv(sprintf('%s=%s', $name, $value));
                        $_ENV[$name] = $value;
                        $_SERVER[$name] = $value;
                    }
                }
            }
        }
    }
}

// Constants
define('APP_NAME', $_ENV['APP_NAME'] ?? 'VisionMetrics');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost:3000');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'visionmetrics');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

define('REDIS_HOST', $_ENV['REDIS_HOST'] ?? '');
define('REDIS_PORT', $_ENV['REDIS_PORT'] ?? 6379);
define('REDIS_ENABLED', filter_var($_ENV['REDIS_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN));

define('ADAPTER_MODE', $_ENV['ADAPTER_MODE'] ?? 'simulate');

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', $_ENV['LOG_PATH'] ?? '/var/www/html/logs/php-errors.log');
}

// Session configuration (secure)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', $_ENV['SESSION_LIFETIME'] ?? 7200);

// HTTPS only in production
if (strpos(APP_URL, 'https://') === 0) {
    ini_set('session.cookie_secure', 1);
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CSP para páginas admin (pode ser sobrescrito em endpoints públicos)
if (!isset($_SERVER['REQUEST_URI']) || strpos($_SERVER['REQUEST_URI'], '/track.php') === false) {
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://chart.googleapis.com https://www.google-analytics.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src 'self';");
}

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Start session if not CLI
if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_name($_ENV['SESSION_NAME'] ?? 'visionmetrics_session');
    session_start();
}

// Helper functions
function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function logMessage($level, $message, $context = []) {
    $logFile = env('LOG_PATH', '/var/www/html/logs') . '/app.log';
    $logEntry = json_encode([
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => $level,
        'message' => $message,
        'context' => $context
    ]) . "\n";
    
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    // Also output to stdout in JSON format for Docker logs
    if (php_sapi_name() === 'cli') {
        echo $logEntry;
    }
}



