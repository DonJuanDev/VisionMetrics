<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VisionMetrics - Verificação de Instalação</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .check-item {
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s;
        }
        .check-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .check-item.success {
            background: #f0fff4;
            border-color: #48bb78;
        }
        .check-item.error {
            background: #fff5f5;
            border-color: #f56565;
        }
        .check-item.warning {
            background: #fffaf0;
            border-color: #ed8936;
        }
        .icon {
            font-size: 32px;
            line-height: 1;
        }
        .check-content {
            flex: 1;
        }
        .check-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
            font-size: 16px;
        }
        .check-description {
            color: #718096;
            font-size: 14px;
            line-height: 1.5;
        }
        .footer {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            color: #718096;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .summary {
            background: #f7fafc;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        .summary-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
        }
        code {
            background: #edf2f7;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificação de Instalação</h1>
        <p class="subtitle">VisionMetrics - Diagnóstico do Sistema</p>

        <?php
        $checks = [];
        $errors = 0;
        $warnings = 0;
        $success = 0;

        // Check 1: PHP Version
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '8.2.0', '>=')) {
            $checks[] = [
                'status' => 'success',
                'icon' => '✅',
                'title' => 'Versão do PHP: ' . $phpVersion,
                'description' => 'PHP 8.2+ detectado. Ótimo!'
            ];
            $success++;
        } else {
            $checks[] = [
                'status' => 'error',
                'icon' => '❌',
                'title' => 'Versão do PHP: ' . $phpVersion,
                'description' => 'Requer PHP 8.2 ou superior. Atualize no painel do Hostinger.'
            ];
            $errors++;
        }

        // Check 2: .env file
        if (file_exists('.env')) {
            $checks[] = [
                'status' => 'success',
                'icon' => '✅',
                'title' => 'Arquivo .env encontrado',
                'description' => 'Configurações carregadas com sucesso.'
            ];
            $success++;
        } else {
            $checks[] = [
                'status' => 'error',
                'icon' => '❌',
                'title' => 'Arquivo .env não encontrado',
                'description' => 'Crie o arquivo .env na raiz com suas configurações. Veja ENV_HOSTINGER_EXAMPLE.txt'
            ];
            $errors++;
        }

        // Check 3: Database connection
        if (file_exists('.env')) {
            $env = parse_ini_file('.env');
            try {
                $pdo = new PDO(
                    "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
                    $env['DB_USER'],
                    $env['DB_PASS']
                );
                $checks[] = [
                    'status' => 'success',
                    'icon' => '✅',
                    'title' => 'Conexão com banco de dados OK',
                    'description' => 'Conectado ao banco: ' . $env['DB_NAME']
                ];
                $success++;
            } catch (PDOException $e) {
                $checks[] = [
                    'status' => 'error',
                    'icon' => '❌',
                    'title' => 'Erro na conexão com banco de dados',
                    'description' => 'Verifique as credenciais no .env: ' . $e->getMessage()
                ];
                $errors++;
            }
        }

        // Check 4: Required extensions
        $requiredExtensions = ['pdo_mysql', 'mbstring', 'json', 'curl', 'openssl'];
        $missingExtensions = [];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }

        if (empty($missingExtensions)) {
            $checks[] = [
                'status' => 'success',
                'icon' => '✅',
                'title' => 'Extensões PHP OK',
                'description' => 'Todas as extensões necessárias estão instaladas.'
            ];
            $success++;
        } else {
            $checks[] = [
                'status' => 'error',
                'icon' => '❌',
                'title' => 'Extensões PHP faltando',
                'description' => 'Ative no hPanel: ' . implode(', ', $missingExtensions)
            ];
            $errors++;
        }

        // Check 5: Vendor folder
        if (file_exists('vendor/autoload.php')) {
            $checks[] = [
                'status' => 'success',
                'icon' => '✅',
                'title' => 'Composer Vendor encontrado',
                'description' => 'Dependências instaladas corretamente.'
            ];
            $success++;
        } else {
            $checks[] = [
                'status' => 'error',
                'icon' => '❌',
                'title' => 'Pasta vendor/ não encontrada',
                'description' => 'Execute: composer install --no-dev e faça upload da pasta vendor/'
            ];
            $errors++;
        }

        // Check 6: Writable directories
        $writableDirs = ['logs', 'uploads'];
        $notWritable = [];
        foreach ($writableDirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            if (!is_writable($dir)) {
                $notWritable[] = $dir;
            }
        }

        if (empty($notWritable)) {
            $checks[] = [
                'status' => 'success',
                'icon' => '✅',
                'title' => 'Permissões de pastas OK',
                'description' => 'logs/ e uploads/ são graváveis.'
            ];
            $success++;
        } else {
            $checks[] = [
                'status' => 'warning',
                'icon' => '⚠️',
                'title' => 'Permissões de pastas',
                'description' => 'Ajuste permissões (755 ou 777): ' . implode(', ', $notWritable)
            ];
            $warnings++;
        }

        // Check 7: .htaccess
        if (file_exists('.htaccess')) {
            $checks[] = [
                'status' => 'success',
                'icon' => '✅',
                'title' => 'Arquivo .htaccess encontrado',
                'description' => 'Regras de reescrita e segurança configuradas.'
            ];
            $success++;
        } else {
            $checks[] = [
                'status' => 'warning',
                'icon' => '⚠️',
                'title' => 'Arquivo .htaccess não encontrado',
                'description' => 'Faça upload do .htaccess para habilitar URLs amigáveis.'
            ];
            $warnings++;
        }

        // Check 8: SSL/HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $checks[] = [
                'status' => 'success',
                'icon' => '✅',
                'title' => 'SSL/HTTPS ativo',
                'description' => 'Conexão segura estabelecida.'
            ];
            $success++;
        } else {
            $checks[] = [
                'status' => 'warning',
                'icon' => '⚠️',
                'title' => 'HTTPS não detectado',
                'description' => 'Instale o certificado SSL no painel do Hostinger.'
            ];
            $warnings++;
        }

        // Summary
        $total = $success + $errors + $warnings;
        $percentage = $total > 0 ? round(($success / $total) * 100) : 0;
        ?>

        <div class="summary">
            <div class="summary-title">📊 Resumo da Verificação</div>
            <p><strong><?php echo $success; ?></strong> verificações passaram ✅</p>
            <p><strong><?php echo $warnings; ?></strong> avisos ⚠️</p>
            <p><strong><?php echo $errors; ?></strong> erros ❌</p>
            <p style="margin-top: 10px; font-size: 18px; font-weight: 600;">
                Status: <?php echo $percentage; ?>% completo
            </p>
        </div>

        <?php foreach ($checks as $check): ?>
            <div class="check-item <?php echo $check['status']; ?>">
                <div class="icon"><?php echo $check['icon']; ?></div>
                <div class="check-content">
                    <div class="check-title"><?php echo $check['title']; ?></div>
                    <div class="check-description"><?php echo $check['description']; ?></div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="footer">
            <?php if ($errors === 0): ?>
                <p style="color: #48bb78; font-weight: 600; font-size: 18px;">🎉 Tudo pronto! Seu sistema está funcionando!</p>
                <a href="/backend/login.php" class="btn">Fazer Login →</a>
            <?php else: ?>
                <p style="color: #f56565; font-weight: 600;">Corrija os erros acima antes de continuar.</p>
                <p style="margin-top: 10px;">Consulte: <code>HOSTINGER_SETUP.md</code></p>
            <?php endif; ?>
            
            <p style="margin-top: 30px; font-size: 14px;">
                <strong>⚠️ IMPORTANTE:</strong> Apague este arquivo após verificar!<br>
                <code>rm check_hostinger.php</code> ou delete via FTP
            </p>
        </div>
    </div>
</body>
</html>


