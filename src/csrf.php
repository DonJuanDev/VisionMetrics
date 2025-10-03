<?php
/**
 * CSRF Protection
 * Generate and validate CSRF tokens
 */

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
        logMessage('WARNING', 'CSRF token validation failed', [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ]);
        
        http_response_code(403);
        die('CSRF token validation failed');
    }
    
    return true;
}

// Auto-verify CSRF on POST requests for admin pages
// Tracking endpoints são excluídos via check de path
if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    !in_array($_SERVER['PHP_SELF'] ?? '', ['/track.php', '/pixel.php', '/webhooks/whatsapp.php', '/mercadopago/webhook.php'])) {
    // csrf_verify(); // Descomente para ativar globalmente
}



