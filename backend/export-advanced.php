<?php
require_once __DIR__ . '/middleware.php';

$db = getDB();

// Handle export
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    $format = $_POST['format'] ?? 'csv';
    $includeTimeline = isset($_POST['include_timeline']);
    $columns = $_POST['columns'] ?? ['id', 'name', 'email', 'phone_number', 'stage', 'score'];
    
    // Get leads with filters
    $stage = $_POST['stage_filter'] ?? 'all';
    
    $query = "SELECT l.* FROM leads l WHERE l.workspace_id = ?";
    $params = [$currentWorkspace['id']];
    
    if ($stage !== 'all') {
        $query .= " AND l.stage = ?";
        $params[] = $stage;
    }
    
    $query .= " ORDER BY l.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $leads = $stmt->fetchAll();
    
    // Export based on format
    if ($format === 'csv') {
        exportCSV($leads, $columns, $includeTimeline);
    } elseif ($format === 'excel') {
        exportExcel($leads, $columns, $includeTimeline);
    } elseif ($format === 'pdf') {
        exportPDF($leads, $columns);
    }
}

function exportCSV($leads, $columns, $includeTimeline) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="leads-export-' . date('Y-m-d-His') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // UTF-8 BOM para Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Header
    $headers = [];
    foreach ($columns as $col) {
        $headers[] = ucfirst(str_replace('_', ' ', $col));
    }
    if ($includeTimeline) {
        $headers[] = 'Timeline';
    }
    
    fputcsv($output, $headers, ';');
    
    // Data
    foreach ($leads as $lead) {
        $row = [];
        foreach ($columns as $col) {
            $row[] = $lead[$col] ?? '';
        }
        
        if ($includeTimeline) {
            // Get timeline for this lead
            global $db;
            $stmt = $db->prepare("SELECT event_type, created_at FROM events WHERE lead_id = ? ORDER BY created_at DESC LIMIT 10");
            $stmt->execute([$lead['id']]);
            $events = $stmt->fetchAll();
            $timeline = implode('; ', array_map(fn($e) => $e['event_type'] . ' (' . date('d/m/Y H:i', strtotime($e['created_at'])) . ')', $events));
            $row[] = $timeline;
        }
        
        fputcsv($output, $row, ';');
    }
    
    fclose($output);
    exit;
}

function exportExcel($leads, $columns, $includeTimeline) {
    // Simple Excel export (XML format)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="leads-export-' . date('Y-m-d-His') . '.xls"');
    
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
    echo '<Worksheet ss:Name="Leads"><Table>';
    
    // Header
    echo '<Row>';
    foreach ($columns as $col) {
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $col))) . '</Data></Cell>';
    }
    echo '</Row>';
    
    // Data
    foreach ($leads as $lead) {
        echo '<Row>';
        foreach ($columns as $col) {
            $value = $lead[$col] ?? '';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($value) . '</Data></Cell>';
        }
        echo '</Row>';
    }
    
    echo '</Table></Worksheet></Workbook>';
    exit;
}

function exportPDF($leads, $columns) {
    // Simple HTML to PDF (browser print)
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Relatório de Leads</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            h1 { color: #4F46E5; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background: #F3F4F6; padding: 12px; text-align: left; border: 1px solid #E5E7EB; }
            td { padding: 10px; border: 1px solid #E5E7EB; }
            @media print {
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <button onclick="window.print()" class="no-print">Imprimir/Salvar como PDF</button>
        <h1>Relatório de Leads - <?= date('d/m/Y H:i') ?></h1>
        <p>Total: <?= count($leads) ?> leads</p>
        <table>
            <thead>
                <tr>
                    <?php foreach ($columns as $col): ?>
                        <th><?= ucfirst(str_replace('_', ' ', $col)) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads as $lead): ?>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <td><?= htmlspecialchars($lead[$col] ?? '') ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exportação Avançada - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>📤 Exportação Avançada de Dados</h1>
            <p>Exporte seus leads em diversos formatos</p>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Configurar Exportação</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Formato</label>
                            <select name="format" required>
                                <option value="csv">CSV (Excel compatível)</option>
                                <option value="excel">Excel (.xls)</option>
                                <option value="pdf">PDF (Relatório)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Filtrar por Etapa</label>
                            <select name="stage_filter">
                                <option value="all">Todas</option>
                                <option value="novo">Novo</option>
                                <option value="contatado">Contatado</option>
                                <option value="qualificado">Qualificado</option>
                                <option value="proposta">Proposta</option>
                                <option value="negociacao">Negociação</option>
                                <option value="ganho">Ganho</option>
                                <option value="perdido">Perdido</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Colunas para Exportar</label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; padding: 16px; background: #F9FAFB; border-radius: 8px;">
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="columns[]" value="id" checked> ID
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="columns[]" value="name" checked> Nome
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="columns[]" value="email" checked> Email
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="columns[]" value="phone_number" checked> Telefone
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="columns[]" value="company"> Empresa
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="columns[]" value="stage" checked> Etapa
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="columns[]" value="score" checked> Score
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="columns[]" value="total_messages"> Total Mensagens
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="columns[]" value="total_sales"> Total Vendas
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="columns[]" value="city"> Cidade
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="columns[]" value="created_at"> Data Criação
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="columns[]" value="last_seen"> Última Atividade
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="include_timeline" value="1">
                            Incluir Timeline de Atividades (apenas CSV/Excel)
                        </label>
                    </div>

                    <button type="submit" name="export" class="btn btn-primary">📥 Exportar Agora</button>
                </form>
            </div>
        </div>

        <!-- Export History (Future: save exports for scheduling) -->
        <div class="card" style="background: #FEF3C7; border-left: 4px solid #F59E0B;">
            <div class="card-body">
                <h3>⏰ Exportação Agendada (Em Desenvolvimento)</h3>
                <p>Em breve você poderá agendar exportações automáticas diárias/semanais/mensais e receber por email!</p>
            </div>
        </div>
    </div>

    <script src="/frontend/js/app.js"></script>
</body>
</html>





