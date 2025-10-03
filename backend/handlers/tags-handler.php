<?php
// Tags handler

if (isset($_POST['create_tag'])) {
    $name = trim($_POST['name']);
    $color = $_POST['color'] ?? '#6366F1';
    $description = trim($_POST['description'] ?? '');
    
    $stmt = $db->prepare("INSERT INTO tags (workspace_id, name, color, description, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$currentWorkspace['id'], $name, $color, $description, $currentUser['id']]);
    
    $_SESSION['success'] = 'Tag criada!';
    redirect('/settings.php?tab=tags');
}

if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM tags WHERE id = ? AND workspace_id = ?");
    $stmt->execute([$_GET['delete'], $currentWorkspace['id']]);
    
    $_SESSION['success'] = 'Tag excluída!';
    redirect('/settings.php?tab=tags');
}





