<?php
require_once __DIR__ . '/middleware.php';

$db = getDB();

// Handle CSV import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $handle = fopen($file['tmp_name'], 'r');
        
        // Get headers
        $headers = fgetcsv($handle);
        $imported = 0;
        $skipped = 0;
        $updated = 0;
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            $row = array_combine($headers, $data);
            
            $email = $row['email'] ?? $row['Email'] ?? null;
            $phone = $row['phone'] ?? $row['Phone'] ?? $row['telefone'] ?? null;
            $name = $row['name'] ?? $row['Name'] ?? $row['nome'] ?? null;
            
            if (!$email && !$phone) {
                $skipped++;
                continue;
            }
            
            // Check if exists
            $stmt = $db->prepare("SELECT id FROM leads WHERE workspace_id = ? AND (email = ? OR phone_number = ?)");
            $stmt->execute([$currentWorkspace['id'], $email, $phone]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing
                $stmt = $db->prepare("UPDATE leads SET name = COALESCE(?, name), email = COALESCE(?, email), phone_number = COALESCE(?, phone_number) WHERE id = ?");
                $stmt->execute([$name, $email, $phone, $existing['id']]);
                $updated++;
            } else {
                // Create new
                $stmt = $db->prepare("INSERT INTO leads (workspace_id, name, email, phone_number, stage) VALUES (?, ?, ?, ?, 'novo')");
                $stmt->execute([$currentWorkspace['id'], $name, $email, $phone]);
                $imported++;
            }
        }
        
        fclose($handle);
        
        $_SESSION['success'] = "✅ Importação concluída! {$imported} criados, {$updated} atualizados, {$skipped} ignorados.";
        redirect('/import-leads.php');
    } else {
        $_SESSION['error'] = 'Erro no upload do arquivo';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Leads - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>📥 Importar Leads</h1>
            <p>Importe leads em massa via CSV ou Excel</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Upload de Arquivo CSV</h2>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Selecione o arquivo CSV</label>
                        <input type="file" name="csv_file" accept=".csv,.txt" required>
                        <small class="help-text">Formatos aceitos: CSV, TXT (separado por vírgula ou ponto-e-vírgula)</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">📤 Importar Leads</button>
                    <a href="/export-leads.php" class="btn btn-secondary">📥 Exportar Leads Atuais</a>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>📝 Formato do Arquivo CSV</h2>
            </div>
            <div class="card-body">
                <p>Seu arquivo CSV deve conter as seguintes colunas (cabeçalhos na primeira linha):</p>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Coluna</th>
                            <th>Obrigatório?</th>
                            <th>Descrição</th>
                            <th>Exemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>email</code></td>
                            <td><span class="badge badge-warning">Recomendado</span></td>
                            <td>Email do lead</td>
                            <td>joao@example.com</td>
                        </tr>
                        <tr>
                            <td><code>phone</code></td>
                            <td><span class="badge badge-warning">Recomendado</span></td>
                            <td>Telefone com código do país</td>
                            <td>+5511999999999</td>
                        </tr>
                        <tr>
                            <td><code>name</code></td>
                            <td>Opcional</td>
                            <td>Nome completo</td>
                            <td>João Silva</td>
                        </tr>
                    </tbody>
                </table>

                <h4 style="margin-top: 20px;">Exemplo de arquivo CSV:</h4>
                <pre class="code-block">name,email,phone
João Silva,joao@example.com,+5511999998888
Maria Santos,maria@example.com,+5511988887777
Pedro Costa,pedro@example.com,+5511977776666</pre>

                <p><a href="/assets/templates/leads_template.csv" download class="btn btn-sm btn-primary">⬇️ Baixar Template CSV</a></p>
            </div>
        </div>

        <div class="card info-card">
            <h3>ℹ️ Regras de Importação</h3>
            <ul>
                <li>✅ Leads com mesmo email ou telefone serão <strong>atualizados</strong> (não duplicados)</li>
                <li>✅ Novos leads serão criados com status <strong>"Novo"</strong></li>
                <li>✅ Linhas sem email E sem telefone serão <strong>ignoradas</strong></li>
                <li>✅ Importações grandes são processadas em background</li>
                <li>✅ Você receberá notificação quando concluir</li>
            </ul>
        </div>
    </div>
</body>
</html>






