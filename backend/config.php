<?php
// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_name(getenv('SESSION_NAME') ?: 'visionmetrics_session');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// Application constants
define('APP_NAME', getenv('APP_NAME') ?: 'VisionMetrics');
define('APP_VERSION', '1.0.0');
define('APP_URL', getenv('APP_URL') ?: 'https://visionmetricsapp.com');

// Database connection
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                getenv('DB_HOST') ?: 'localhost',
                getenv('DB_PORT') ?: '3306',
                getenv('DB_NAME') ?: 'visionmetrics'
            );
            
            $pdo = new PDO(
                $dsn,
                getenv('DB_USER') ?: 'root',
                getenv('DB_PASS') ?: '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            if (getenv('APP_DEBUG') === 'true') {
                die('Database connection error: ' . $e->getMessage());
            }
            die('Database connection error. Please check your configuration.');
        }
    }
    
    return $pdo;
}

// Helper functions
function redirect($path) {
    header('Location: ' . $path);
    exit;
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function setCurrentWorkspace($workspaceId) {
    $_SESSION['workspace_id'] = $workspaceId;
}

function getCurrentWorkspace() {
    if (!isset($_SESSION['workspace_id'])) {
        return null;
    }
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM workspaces WHERE id = ?");
    $stmt->execute([$_SESSION['workspace_id']]);
    return $stmt->fetch();
}

function getUserWorkspaces($userId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT w.* FROM workspaces w
        JOIN workspace_members wm ON w.id = wm.workspace_id
        WHERE wm.user_id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// Helper function for time ago
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'Agora mesmo';
    if ($diff < 3600) return floor($diff / 60) . ' min atrás';
    if ($diff < 86400) return floor($diff / 3600) . ' h atrás';
    if ($diff < 604800) return floor($diff / 86400) . ' dias atrás';
    
    return date('d/m/Y', $timestamp);
}

// Helper function for phone formatting
function formatPhone($phone) {
    if (empty($phone)) return 'N/A';
    return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', preg_replace('/\D/', '', $phone));
}

// Helper function for currency formatting
function formatCurrency($value) {
    if (is_null($value) || $value === '') return 'R$ 0,00';
    return 'R$ ' . number_format($value, 2, ',', '.');
}

// Helper function to generate random slug
function generateSlug($length = 8) {
    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $slug = '';
    for ($i = 0; $i < $length; $i++) {
        $slug .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $slug;
}

// CSRF Protection Functions
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return true;
    }
    
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    
    if (empty($token) || empty($sessionToken) || !hash_equals($sessionToken, $token)) {
        http_response_code(403);
        die('CSRF token validation failed');
    }
    
    return true;
}

// Rate Limiting Functions
function checkRateLimit($identifier, $maxAttempts = 5, $windowMinutes = 15) {
    $db = getDB();
    
    // Clean old attempts
    $stmt = $db->prepare("DELETE FROM login_attempts WHERE created_at < DATE_SUB(NOW(), INTERVAL ? MINUTE)");
    $stmt->execute([$windowMinutes]);
    
    // Count recent attempts
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM login_attempts WHERE identifier = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
    $stmt->execute([$identifier, $windowMinutes]);
    $result = $stmt->fetch();
    
    return $result['count'] < $maxAttempts;
}

function recordLoginAttempt($identifier, $success = false) {
    $db = getDB();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $stmt = $db->prepare("INSERT INTO login_attempts (identifier, ip_address, success, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$identifier, $ip, $success ? 1 : 0]);
}

// Email Functions
function sendEmail($to, $subject, $body, $isHTML = true) {
    // Simple email sending - in production, use PHPMailer or similar
    $headers = "From: " . getenv('MAIL_FROM') . "\r\n";
    $headers .= "Reply-To: " . getenv('MAIL_FROM') . "\r\n";
    
    if ($isHTML) {
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    }
    
    return mail($to, $subject, $body, $headers);
}

// Password Reset Functions
function generatePasswordResetToken() {
    return bin2hex(random_bytes(32));
}

function createPasswordResetRecord($email, $token) {
    $db = getDB();
    
    // Remove old tokens for this email
    $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt->execute([$email]);
    
    // Create new token
    $stmt = $db->prepare("INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, NOW())");
    return $stmt->execute([$email, $token]);
}

function validatePasswordResetToken($token) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute([$token]);
    return $stmt->fetch();
}

function deletePasswordResetToken($token) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM password_resets WHERE token = ?");
    return $stmt->execute([$token]);
}

// Email Verification Functions
function generateEmailVerificationToken() {
    return bin2hex(random_bytes(32));
}

function createEmailVerificationRecord($userId, $token) {
    $db = getDB();
    
    // Remove old tokens for this user
    $stmt = $db->prepare("DELETE FROM email_verifications WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // Create new token
    $stmt = $db->prepare("INSERT INTO email_verifications (user_id, token, created_at) VALUES (?, ?, NOW())");
    return $stmt->execute([$userId, $token]);
}

function validateEmailVerificationToken($token) {
    $db = getDB();
    $stmt = $db->prepare("SELECT u.* FROM email_verifications ev JOIN users u ON ev.user_id = u.id WHERE ev.token = ? AND ev.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stmt->execute([$token]);
    return $stmt->fetch();
}

function deleteEmailVerificationToken($token) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM email_verifications WHERE token = ?");
    return $stmt->execute([$token]);
}

// Geolocation Functions
function getGeolocationFromIP($ipAddress) {
    // Skip private/local IPs
    if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return ['country' => null, 'region' => null, 'city' => null];
    }
    
    // Try MaxMind GeoIP2 first (if available)
    if (class_exists('GeoIp2\Database\Reader')) {
        try {
            $reader = new GeoIp2\Database\Reader('/usr/share/GeoIP/GeoLite2-City.mmdb');
            $record = $reader->city($ipAddress);
            return [
                'country' => $record->country->name ?? null,
                'region' => $record->subdivisions[0]->name ?? null,
                'city' => $record->city->name ?? null
            ];
        } catch (Exception $e) {
            // Fallback to API
        }
    }
    
    // Fallback to free IP API
    try {
        $apiKey = getenv('IPAPI_KEY'); // Optional API key for ipapi.co
        $url = $apiKey ? 
            "http://ipapi.co/{$ipAddress}/json/?key={$apiKey}" : 
            "http://ipapi.co/{$ipAddress}/json/";
            
        $response = file_get_contents($url, false, stream_context_create([
            'http' => ['timeout' => 2]
        ]));
        
        if ($response) {
            $data = json_decode($response, true);
            if ($data && !isset($data['error'])) {
                return [
                    'country' => $data['country_name'] ?? null,
                    'region' => $data['region'] ?? null,
                    'city' => $data['city'] ?? null
                ];
            }
        }
    } catch (Exception $e) {
        // Silent fail
    }
    
    return ['country' => null, 'region' => null, 'city' => null];
}