<?php
require_once __DIR__ . '/../middleware.php';

$db = getDB();

// Handle report generation
if (isset($_POST['generate_report'])) {
    $reportType = $_POST['report_type'];
    
    if ($reportType === 'conversations') {
        // Generate conversations report
        $stmt = $db->prepare("
            SELECT c.*, 
                   l.name as lead_name,
                   (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id) as total_messages
            FROM conversations c
            LEFT JOIN leads l ON c.lead_id = l.id
            WHERE c.workspace_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$currentWorkspace['id']]);
        $data = $stmt->fetchAll();
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="conversas-' . date('Ymd-His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['Data', 'Contato', 'Telefone', 'Origem', 'Mensagens', 'É Venda', 'Etapa'], ';');
        
        foreach ($data as $row) {
            fputcsv($output, [
                date('d/m/Y H:i', strtotime($row['created_at'])),
                $row['contact_name'] ?? 'Sem nome',
                $row['contact_phone'],
                $row['utm_source'] ?? 'Direto',
                $row['total_messages'] ?? 0,
                $row['is_sale'] ? 'Sim' : 'Não',
                $row['journey_stage'] ?? 'awareness'
            ], ';');
        }
        
        fclose($output);
        exit;
    } elseif ($reportType === 'gclid') {
        // Generate offline conversions with GCLID
        $stmt = $db->prepare("
            SELECT 
                c.gclid,
                c.contact_name,
                c.contact_phone,
                c.created_at as conversion_time
            FROM conversations c
            WHERE c.workspace_id = ? AND c.gclid IS NOT NULL AND c.is_sale = 1
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$currentWorkspace['id']]);
        $data = $stmt->fetchAll();
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="conversoes-offline-gclid-' . date('Ymd-His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['GCLID', 'Conversion Time', 'Conversion Value', 'Conversion Currency'], ';');
        
        foreach ($data as $row) {
            fputcsv($output, [
                $row['gclid'],
                date('Y-m-d H:i:s', strtotime($row['conversion_time'])),
                '100.00', // Valor padrão, pode ser configurado
                'BRL'
            ], ';');
        }
        
        fclose($output);
        exit;
    } elseif ($reportType === 'leads') {
        // Generate leads report
        $stmt = $db->prepare("
            SELECT * FROM leads 
            WHERE workspace_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$currentWorkspace['id']]);
        $data = $stmt->fetchAll();
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="leads-' . date('Ymd-His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['Nome', 'Email', 'Telefone', 'Empresa', 'Origem', 'Campanha', 'Status', 'Data'], ';');
        
        foreach ($data as $row) {
            fputcsv($output, [
                $row['name'] ?? '',
                $row['email'] ?? '',
                $row['phone_number'] ?? '',
                $row['company'] ?? '',
                $row['utm_source'] ?? '',
                $row['utm_campaign'] ?? '',
                $row['status'],
                date('d/m/Y H:i', strtotime($row['created_at']))
            ], ';');
        }
        
        fclose($output);
        exit;
    }
}

// Get available reports
$reports = [
    [
        'icon' => '💬',
        'name' => 'Conversas',
        'type' => 'conversations',
        'description' => 'Exportar todas as conversas com origem e status'
    ],
    [
        'icon' => '📊',
        'name' => 'Conversões Offline com GCLID',
        'type' => 'gclid',
        'description' => 'Arquivo formatado para importação no Google Ads'
    ],
    [
        'icon' => '📈',
        'name' => 'Histórico de Alterações da Jornada de Compra',
        'type' => 'journey',
        'description' => 'Timeline de mudanças de etapa dos leads'
    ],
    [
        'icon' => '💰',
        'name' => 'Histórico de Vendas',
        'type' => 'sales',
        'description' => 'Relatório completo de todas as vendas'
    ]
];

// Check report history
$stmt = $db->prepare("
    SELECT 'Histórico De Vendas' as type, created_at, 'Processado Com Sucesso' as status
    FROM sales
    WHERE workspace_id = ?
    ORDER BY created_at DESC
    LIMIT 10
");
$stmt->execute([$currentWorkspace['id']]);
$reportHistory = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Relatórios</h1>
                    <p>Gere e baixe relatórios customizados</p>
                </div>
            </div>

            <div class="container">
                <div class="card" style="background: #EFF6FF; border-left: 4px solid #3B82F6; margin-bottom: 20px;">
                    <div class="card-body">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <svg width="20" height="20" fill="#3B82F6" viewBox="0 0 24 24">
                                <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <strong style="color: #1E40AF;">Dúvidas sobre Relatórios?</strong>
                                <p style="margin: 4px 0 0; font-size: 14px; color: #1E40AF;">
                                    🎥 <a href="#" style="color: #3B82F6; font-weight: 600;">Saiba mais no vídeo</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <h3 style="margin-bottom: 20px; font-size: 18px;">Gerar Relatórios</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-bottom: 40px;">
                    <?php foreach ($reports as $report): ?>
                        <div class="card">
                            <div class="card-body" style="text-align: center; padding: 32px 20px;">
                                <div style="font-size: 48px; margin-bottom: 16px;"><?= $report['icon'] ?></div>
                                <h3 style="margin-bottom: 8px; font-size: 16px;"><?= $report['name'] ?></h3>
                                <p style="color: #6B7280; font-size: 13px; margin-bottom: 20px; min-height: 40px;">
                                    <?= $report['description'] ?>
                                </p>
                                <form method="POST">
                                    <input type="hidden" name="report_type" value="<?= $report['type'] ?>">
                                    <button type="submit" name="generate_report" class="btn btn-primary">
                                        📥 Gerar e Baixar
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h3 style="margin-bottom: 20px; font-size: 18px;">Histórico</h3>
                
                <div class="card">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Status do processamento</th>
                                    <th>Download</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reportHistory)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <p style="padding: 40px; color: #6B7280;">Nenhum relatório gerado ainda</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($reportHistory as $hist): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i:s', strtotime($hist['created_at'])) ?></td>
                                            <td><strong><?= htmlspecialchars($hist['type']) ?></strong></td>
                                            <td>
                                                <span class="badge badge-success">Processado Com Sucesso</span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" disabled>
                                                    ⬇️
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/frontend/js/app.js"></script>
</body>
</html>




