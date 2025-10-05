<?php
// Teste de conexão com banco de dados
echo "<h1>Teste de Conexão com Banco de Dados</h1>";

// Carregar variáveis de ambiente
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    echo "<p style='color: green;'>✅ Arquivo .env carregado com sucesso!</p>";
} else {
    echo "<p style='color: red;'>❌ Arquivo .env NÃO encontrado!</p>";
}

// Mostrar configurações
echo "<h2>Configurações atuais:</h2>";
echo "<p><strong>DB_HOST:</strong> " . (getenv('DB_HOST') ?: 'NÃO DEFINIDO') . "</p>";
echo "<p><strong>DB_NAME:</strong> " . (getenv('DB_NAME') ?: 'NÃO DEFINIDO') . "</p>";
echo "<p><strong>DB_USER:</strong> " . (getenv('DB_USER') ?: 'NÃO DEFINIDO') . "</p>";
echo "<p><strong>DB_PASS:</strong> " . (getenv('DB_PASS') ? '***DEFINIDO***' : 'NÃO DEFINIDO') . "</p>";
echo "<p><strong>DB_PORT:</strong> " . (getenv('DB_PORT') ?: 'NÃO DEFINIDO') . "</p>";

// Testar conexão
try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        getenv('DB_HOST') ?: 'localhost',
        getenv('DB_PORT') ?: '3306',
        getenv('DB_NAME') ?: 'visionmetrics'
    );
    
    $pdo = new PDO(
        $dsn,
        getenv('DB_USER') ?: 'root',
        getenv('DB_PASS') ?: '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 CONEXÃO COM BANCO DE DADOS FUNCIONANDO!</p>";
    
    // Testar uma query simples
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✅ Query de teste executada com sucesso!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ ERRO DE CONEXÃO:</p>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    
    echo "<h2>Possíveis soluções:</h2>";
    echo "<ul>";
    echo "<li>Verificar se o arquivo .env está na raiz do projeto</li>";
    echo "<li>Verificar se as credenciais do banco estão corretas</li>";
    echo "<li>Verificar se o banco de dados existe no Hostinger</li>";
    echo "<li>Verificar se o host do banco está correto (mysql.hostinger.com)</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Voltar para a página inicial</a></p>";
?>
