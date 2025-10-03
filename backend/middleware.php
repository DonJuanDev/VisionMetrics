<?php
require_once __DIR__ . '/config.php';

// Se não está logado, redireciona para login
if (!isset($_SESSION['user_id'])) {
    redirect('/backend/login.php');
}

$currentUser = getCurrentUser();

// Se usuário não existe, logout
if (!$currentUser) {
    session_destroy();
    redirect('/backend/login.php');
}

// Verificar workspace
if (!isset($_SESSION['workspace_id'])) {
    $workspaces = getUserWorkspaces($currentUser['id']);
    if (empty($workspaces)) {
        // Criar workspace padrão
        $db = getDB();
        $slug = 'ws-' . uniqid();
        $stmt = $db->prepare("INSERT INTO workspaces (name, slug, owner_id, plan) VALUES (?, ?, ?, 'pro')");
        $stmt->execute(['Meu Workspace', $slug, $currentUser['id']]);
        $workspaceId = $db->lastInsertId();
        
        $stmt = $db->prepare("INSERT INTO workspace_members (workspace_id, user_id, role) VALUES (?, ?, 'owner')");
        $stmt->execute([$workspaceId, $currentUser['id']]);
        
        setCurrentWorkspace($workspaceId);
    } else {
        setCurrentWorkspace($workspaces[0]['id']);
    }
}

$currentWorkspace = getCurrentWorkspace();