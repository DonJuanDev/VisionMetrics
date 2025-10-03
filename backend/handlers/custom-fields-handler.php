<?php
// Custom fields handler

if (isset($_POST['create_field'])) {
    $name = trim($_POST['name']);
    $fieldKey = strtolower(preg_replace('/[^a-z0-9]+/', '_', $name));
    $fieldType = $_POST['field_type'];
    $isRequired = isset($_POST['is_required']) ? 1 : 0;
    
    $stmt = $db->prepare("INSERT INTO custom_fields (workspace_id, name, field_key, field_type, is_required) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$currentWorkspace['id'], $name, $fieldKey, $fieldType, $isRequired]);
    
    $_SESSION['success'] = 'Campo criado!';
    redirect('/settings.php?tab=fields');
}

if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM custom_fields WHERE id = ? AND workspace_id = ?");
    $stmt->execute([$_GET['delete'], $currentWorkspace['id']]);
    
    $_SESSION['success'] = 'Campo excluído!';
    redirect('/settings.php?tab=fields');
}





