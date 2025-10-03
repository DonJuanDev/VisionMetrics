<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Application constants
define('APP_NAME', 'VisionMetrics');
define('APP_VERSION', '1.0.0');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:3000');

// Database connection
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                getenv('DB_HOST') ?: 'mysql',
                getenv('DB_PORT') ?: '3306',
                getenv('DB_NAME') ?: 'visionmetrics'
            );
            
            $pdo = new PDO(
                $dsn,
                getenv('DB_USER') ?: 'visionmetrics',
                getenv('DB_PASS') ?: 'visionmetrics',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die('Database error');
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

// Helper function to generate random slug
function generateSlug($length = 8) {
    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $slug = '';
    for ($i = 0; $i < $length; $i++) {
        $slug .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $slug;
}