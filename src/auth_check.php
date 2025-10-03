<?php
/**
 * Authentication check middleware
 * Protege páginas administrativas
 */

// Verificar se está logado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/dashboard.php';
    redirect('/login.php');
}

// Verificar se tem workspace selecionado
if (!isset($_SESSION['workspace_id'])) {
    redirect('/select-workspace.php');
}

// Carregar dados do usuário e workspace
$currentUser = null;
$currentWorkspace = null;

try {
    $db = getDB();
    
    // Get user
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch();
    
    if (!$currentUser) {
        session_destroy();
        redirect('/login.php');
    }
    
    // Get workspace
    $stmt = $db->prepare("SELECT * FROM workspaces WHERE id = ?");
    $stmt->execute([$_SESSION['workspace_id']]);
    $currentWorkspace = $stmt->fetch();
    
    if (!$currentWorkspace) {
        unset($_SESSION['workspace_id']);
        redirect('/select-workspace.php');
    }
    
    // Verify user has access to workspace
    $stmt = $db->prepare("
        SELECT role FROM workspace_members 
        WHERE workspace_id = ? AND user_id = ?
    ");
    $stmt->execute([$currentWorkspace['id'], $currentUser['id']]);
    $membership = $stmt->fetch();
    
    if (!$membership) {
        logMessage('WARNING', 'Unauthorized workspace access attempt', [
            'user_id' => $currentUser['id'],
            'workspace_id' => $currentWorkspace['id']
        ]);
        unset($_SESSION['workspace_id']);
        redirect('/select-workspace.php');
    }
    
    // Store role in session
    $_SESSION['user_role'] = $membership['role'];
    
} catch (Exception $e) {
    logMessage('ERROR', 'Auth check failed', ['error' => $e->getMessage()]);
    http_response_code(500);
    die('Authentication error');
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasRole($requiredRole) {
    $roleHierarchy = ['member' => 1, 'admin' => 2, 'owner' => 3];
    $userRole = $_SESSION['user_role'] ?? 'member';
    
    return ($roleHierarchy[$userRole] ?? 0) >= ($roleHierarchy[$requiredRole] ?? 99);
}

function requireRole($requiredRole) {
    if (!hasRole($requiredRole)) {
        http_response_code(403);
        die('Insufficient permissions');
    }
}



