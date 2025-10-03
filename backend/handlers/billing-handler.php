<?php
// Billing handler

if (isset($_POST['upgrade_plan'])) {
    $selectedPlan = $_POST['plan'] ?? 'free';
    
    $stmt = $db->prepare("UPDATE workspaces SET plan = ? WHERE id = ?");
    $stmt->execute([$selectedPlan, $currentWorkspace['id']]);
    
    $_SESSION['success'] = 'Plano atualizado!';
    redirect('/settings.php?tab=billing');
}





