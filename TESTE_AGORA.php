<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>TESTE RÁPIDO</title>
    <style>
        body { font-family: Arial; padding: 30px; background: #1a1a1a; color: white; }
        .box { background: #2a2a2a; padding: 20px; margin: 20px 0; border-radius: 10px; border-left: 5px solid #10B981; }
        .error { border-left-color: #ef4444; background: #3a1a1a; }
        .success { border-left-color: #10B981; background: #1a3a1a; }
        h1 { color: #10B981; }
        code { background: #1a1a1a; padding: 5px 10px; border-radius: 5px; display: inline-block; margin: 5px 0; }
        .btn { background: #10B981; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; font-weight: bold; }
    </style>
</head>
<body>

<h1>🔥 TESTE URGENTE - VisionMetrics</h1>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CREDENCIAIS CORRETAS
$DB_HOST = 'localhost';
$DB_NAME = 'u604248417_visionmetrics';
$DB_USER = 'u604248417_visionmetrics';
$DB_PASS = '182876JJj?';
$DB_PORT = '3306';

echo "<div class='box'>";
echo "<h2>📋 Usando Credenciais:</h2>";
echo "<code>Host: $DB_HOST</code><br>";
echo "<code>Banco: $DB_NAME</code><br>";
echo "<code>Usuário: $DB_USER</code><br>";
echo "<code>Senha: " . str_repeat('*', strlen($DB_PASS)) . "</code><br>";
echo "<code>Porta: $DB_PORT</code><br>";
echo "</div>";

// TESTAR CONEXÃO
echo "<div class='box'>";
echo "<h2>🔌 Testando Conexão...</h2>";

try {
    $dsn = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4";
    
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<div class='success' style='font-size: 20px; padding: 30px; text-align: center;'>";
    echo "✅ ✅ ✅<br><br>";
    echo "<strong>CONEXÃO FUNCIONOU!</strong><br><br>";
    echo "O banco de dados está ACESSÍVEL!<br><br>";
    echo "</div>";
    
    // VERIFICAR TABELAS
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) == 0) {
        echo "<div class='error'>";
        echo "<h2>⚠️ BANCO VAZIO - PRECISA IMPORTAR!</h2>";
        echo "<p><strong>FAÇA ISSO AGORA:</strong></p>";
        echo "<ol style='line-height: 2;'>";
        echo "<li>Abra o hPanel da Hostinger</li>";
        echo "<li>Vá em: <strong>Databases → phpMyAdmin</strong></li>";
        echo "<li>Clique no banco: <code>u604248417_visionmetrics</code></li>";
        echo "<li>Clique na aba <strong>Import</strong></li>";
        echo "<li>Escolha o arquivo: <code>sql/schema.sql</code> ou <code>INSTALACAO_COMPLETA.sql</code></li>";
        echo "<li>Clique em <strong>Go</strong></li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div class='success'>";
        echo "<h2>✅ Banco tem " . count($tables) . " tabelas:</h2>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        if (in_array('users', $tables)) {
            echo "<div style='background: #1a3a1a; padding: 30px; margin: 20px 0; border-radius: 10px; text-align: center; font-size: 24px;'>";
            echo "🎉🎉🎉<br><br>";
            echo "<strong>TUDO PRONTO!</strong><br><br>";
            echo "Pode criar sua conta agora!<br><br>";
            echo "<a href='/backend/register.php' class='btn' style='font-size: 20px;'>CRIAR CONTA AGORA →</a>";
            echo "</div>";
        } else {
            echo "<div class='error'><strong>❌ Falta importar a tabela 'users'!</strong></div>";
        }
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>❌ ERRO DE CONEXÃO:</h2>";
    echo "<pre style='background: #1a1a1a; padding: 20px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars($e->getMessage());
    echo "</pre>";
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "<h3>🔴 SENHA OU USUÁRIO ERRADO!</h3>";
        echo "<p><strong>VERIFIQUE no hPanel:</strong></p>";
        echo "<ol>";
        echo "<li>Entre em: Databases → MySQL Databases</li>";
        echo "<li>Veja o nome EXATO do banco</li>";
        echo "<li>Veja o usuário EXATO</li>";
        echo "<li>Se necessário, RESET a senha</li>";
        echo "</ol>";
    }
    
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "<h3>🔴 BANCO DE DADOS NÃO EXISTE!</h3>";
        echo "<p>Crie o banco <code>u604248417_visionmetrics</code> no hPanel</p>";
    }
    
    echo "</div>";
}

echo "</div>";

?>

<div class='box'>
    <h2>📌 Links Úteis</h2>
    <a href="/" class="btn">← Home</a>
    <a href="/backend/register.php" class="btn">Criar Conta</a>
    <a href="https://hpanel.hostinger.com" class="btn" target="_blank">Abrir hPanel</a>
</div>

</body>
</html>

