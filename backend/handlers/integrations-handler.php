<?php
// Integrations handler - redirect to dedicated page for complex configs

if (isset($_POST['save_integration'])) {
    $_SESSION['info'] = 'Use a página de Integrações Completas para configurar';
    redirect('/integrations-config.php');
}





