<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico VisionMetrics</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #333; border-bottom: 3px solid #10B981; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; background: #fff; padding: 15px; border-left: 4px solid #3B82F6; }
        .success { background: #d4edda; border: 1px solid #28a745; padding: 15px; margin: 10px 0; border-radius: 5px; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #dc3545; padding: 15px; margin: 10px 0; border-radius: 5px; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #0c5460; padding: 15px; margin: 10px 0; border-radius: 5px; color: #0c5460; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
        ul { line-height: 1.8; }
        .btn { display: inline-block; background: #10B981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #059669; }
    </style>
</head>
<body>

<h1>🔍 Diagnóstico Completo - VisionMetrics</h1>

<?php
// 1. VERIFICAR ARQUIVO .ENV
echo "<h2>1️⃣ Verificando Arquivo .env</h2>";
$envPath = __DIR__ . '/.env';

if (file_exists($envPath)) {
    echo "<div class='success'>✅ Arquivo .env EXISTE em: <code>$envPath</code></div>";
    
    // Carregar variáveis
    $envContent = file_get_contents($envPath);
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        putenv(sprintf('%s=%s', $name, $value));
        $_ENV[$name] = $value;
    }
    
    echo "<div class='success'>✅ Variáveis de ambiente carregadas</div>";
} else {
    echo "<div class='error'>❌ <strong>ARQUIVO .ENV NÃO ENCONTRADO!</strong><br>";
    echo "Procurado em: <code>$envPath</code><br><br>";
    echo "<strong>SOLUÇÃO:</strong> Criar o arquivo .env na raiz com as credenciais do banco</div>";
    exit;
}

// 2. VERIFICAR VARIÁVEIS
echo "<h2>2️⃣ Verificando Variáveis do Banco de Dados</h2>";
$dbHost = getenv('DB_HOST') ?: 'NÃO DEFINIDO';
$dbName = getenv('DB_NAME') ?: 'NÃO DEFINIDO';
$dbUser = getenv('DB_USER') ?: 'NÃO DEFINIDO';
$dbPass = getenv('DB_PASS') ?: 'NÃO DEFINIDO';
$dbPort = getenv('DB_PORT') ?: '3306';

echo "<div class='info'>";
echo "🖥️ <strong>DB_HOST:</strong> <code>$dbHost</code><br>";
echo "🗄️ <strong>DB_NAME:</strong> <code>$dbName</code><br>";
echo "👤 <strong>DB_USER:</strong> <code>$dbUser</code><br>";
echo "🔐 <strong>DB_PASS:</strong> <code>" . (strlen($dbPass) > 0 ? str_repeat('*', min(strlen($dbPass), 10)) : 'NÃO DEFINIDO') . "</code><br>";
echo "🔌 <strong>DB_PORT:</strong> <code>$dbPort</code><br>";
echo "</div>";

if ($dbHost === 'NÃO DEFINIDO' || $dbName === 'NÃO DEFINIDO') {
    echo "<div class='error'>❌ Variáveis não estão definidas corretamente!</div>";
    exit;
}

// 3. TESTAR CONEXÃO
echo "<h2>3️⃣ Testando Conexão com MySQL</h2>";

try {
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
    echo "<div class='info'>📡 Tentando conectar...<br>DSN: <code>$dsn</code></div>";
    
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<div class='success'><strong>✅ CONEXÃO COM BANCO DE DADOS ESTABELECIDA!</strong></div>";
    
    // 4. VERIFICAR TABELAS
    echo "<h2>4️⃣ Verificando Estrutura do Banco</h2>";
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) == 0) {
        echo "<div class='error'>";
        echo "❌ <strong>BANCO DE DADOS ESTÁ VAZIO!</strong><br><br>";
        echo "<strong>SOLUÇÃO URGENTE:</strong><br>";
        echo "1. Acesse o phpMyAdmin no hPanel da Hostinger<br>";
        echo "2. Selecione o banco <code>$dbName</code><br>";
        echo "3. Vá na aba <strong>Importar</strong><br>";
        echo "4. Escolha o arquivo <code>sql/schema.sql</code><br>";
        echo "5. Clique em <strong>Executar</strong><br>";
        echo "</div>";
    } else {
        echo "<div class='success'>✅ Banco contém <strong>" . count($tables) . " tabelas</strong>:</div>";
        echo "<div class='info'><ul>";
        foreach ($tables as $table) {
            echo "<li>📋 $table</li>";
        }
        echo "</ul></div>";
        
        // Verificar tabela users
        if (in_array('users', $tables)) {
            echo "<div class='success'>✅ Tabela <code>users</code> existe!</div>";
            
            // Verificar estrutura da tabela users
            $columns = $pdo->query("DESCRIBE users")->fetchAll();
            echo "<div class='info'><strong>Colunas da tabela users:</strong><ul>";
            foreach ($columns as $col) {
                echo "<li>{$col['Field']} ({$col['Type']})</li>";
            }
            echo "</ul></div>";
            
            echo "<div class='success' style='font-size: 18px;'>";
            echo "🎉 <strong>TUDO CONFIGURADO CORRETAMENTE!</strong><br><br>";
            echo "O sistema está pronto para criar contas!";
            echo "</div>";
            
        } else {
            echo "<div class='error'>❌ Tabela <code>users</code> NÃO foi encontrada!<br>";
            echo "Você precisa importar o arquivo <code>sql/schema.sql</code></div>";
        }
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<strong>❌ ERRO AO CONECTAR NO BANCO DE DADOS:</strong><br><br>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<br><strong>POSSÍVEIS CAUSAS:</strong><br>";
    echo "<ul>";
    echo "<li>Credenciais incorretas (usuário ou senha errados)</li>";
    echo "<li>Banco de dados não existe no servidor</li>";
    echo "<li>Host incorreto (deve ser 'localhost' na Hostinger)</li>";
    echo "<li>MySQL não está rodando</li>";
    echo "</ul>";
    echo "<br><strong>VERIFIQUE no hPanel da Hostinger:</strong><br>";
    echo "1. Databases → MySQL Databases<br>";
    echo "2. Confirme nome do banco, usuário e senha<br>";
    echo "3. Se necessário, recrie o usuário<br>";
    echo "</div>";
}

?>

<h2>📋 Links Úteis</h2>
<a href="/" class="btn">← Voltar para Home</a>
<a href="/backend/register.php" class="btn" style="background: #3B82F6;">Criar Conta →</a>
<a href="/backend/login.php" class="btn" style="background: #8B5CF6;">Fazer Login →</a>

</body>
</html>


