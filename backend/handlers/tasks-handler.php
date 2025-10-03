<?php
// Tasks handler

// Create task
if (isset($_POST['create_task'])) {
    $title = trim($_POST['title']);
    $leadId = $_POST['lead_id'] ?? null;
    $assignedTo = $_POST['assigned_to'] ?? null;
    $priority = $_POST['priority'] ?? 'medium';
    $dueDate = $_POST['due_date'] ?? null;
    
    $stmt = $db->prepare("
        INSERT INTO tasks (workspace_id, lead_id, assigned_to, created_by, title, priority, due_date)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$currentWorkspace['id'], $leadId, $assignedTo, $currentUser['id'], $title, $priority, $dueDate]);
    
    $_SESSION['success'] = 'Tarefa criada!';
    redirect('/automation.php?tab=tasks');
}

// Complete task
if (isset($_GET['complete_task'])) {
    $stmt = $db->prepare("UPDATE tasks SET status = 'completed', completed_at = NOW() WHERE id = ? AND workspace_id = ?");
    $stmt->execute([$_GET['complete_task'], $currentWorkspace['id']]);
    $_SESSION['success'] = 'Tarefa concluída!';
    redirect('/automation.php?tab=tasks');
}

// Get tasks
$filter = $_GET['task_filter'] ?? 'pending';
$stmt = $db->prepare("
    SELECT t.*, 
           l.name as lead_name,
           u.name as assigned_name
    FROM tasks t
    LEFT JOIN leads l ON t.lead_id = l.id
    LEFT JOIN users u ON t.assigned_to = u.id
    WHERE t.workspace_id = ?
    " . ($filter !== 'all' ? "AND t.status = ?" : "") . "
    ORDER BY 
        CASE t.priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END,
        t.due_date ASC
");

if ($filter !== 'all') {
    $stmt->execute([$currentWorkspace['id'], $filter]);
} else {
    $stmt->execute([$currentWorkspace['id']]);
}

$tasks = $stmt->fetchAll();

// Get team for assignment
$stmt = $db->prepare("SELECT u.id, u.name FROM users u INNER JOIN workspace_members wm ON u.id = wm.user_id WHERE wm.workspace_id = ?");
$stmt->execute([$currentWorkspace['id']]);
$teamMembers = $stmt->fetchAll();

$stmt = $db->prepare("SELECT id, name FROM leads WHERE workspace_id = ? ORDER BY last_seen DESC LIMIT 100");
$stmt->execute([$currentWorkspace['id']]);
$leadsForTasks = $stmt->fetchAll();





