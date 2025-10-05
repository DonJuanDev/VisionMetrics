<?php
// Arquivo de diagnóstico para testar conexão com banco de dados
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnóstico VisionMetrics</h1>";
echo "<hr>";

// 1. Verificar se arquivo .env existe
echo "<h2>1. Verificando arquivo .env</h2>";
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "✅ Arquivo .env EXISTE<br>";
    echo "📍 Localização: " . $envPath . "<br>";
    
    // Ler e carregar variáveis
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        putenv(sprintf('%s=%s', $name, $value));
        $_ENV[$name] = $value;
    }
    echo "✅ Variáveis carregadas<br>";
} else {
    echo "❌ Arquivo .env NÃO EXISTE<br>";
    echo "📍 Procurado em: " . $envPath . "<br>";
    echo "<strong>SOLUÇÃO: Criar o arquivo .env na raiz do projeto</strong><br>";
}

echo "<hr>";

// 2. Verificar variáveis de ambiente
echo "<h2>2. Variáveis de Banco de Dados</h2>";
$dbHost = getenv('DB_HOST') ?: 'NÃO DEFINIDO';
$dbName = getenv('DB_NAME') ?: 'NÃO DEFINIDO';
$dbUser = getenv('DB_USER') ?: 'NÃO DEFINIDO';
$dbPass = getenv('DB_PASS') ?: 'NÃO DEFINIDO';
$dbPort = getenv('DB_PORT') ?: '3306';

echo "🖥️ DB_HOST: <strong>" . htmlspecialchars($dbHost) . "</strong><br>";
echo "🗄️ DB_NAME: <strong>" . htmlspecialchars($dbName) . "</strong><br>";
echo "👤 DB_USER: <strong>" . htmlspecialchars($dbUser) . "</strong><br>";
echo "🔐 DB_PASS: <strong>" . (strlen($dbPass) > 0 ? str_repeat('*', strlen($dbPass)) : 'NÃO DEFINIDO') . "</strong><br>";
echo "🔌 DB_PORT: <strong>" . htmlspecialchars($dbPort) . "</strong><br>";

echo "<hr>";

// 3. Tentar conexão com o banco
echo "<h2>3. Testando Conexão com Banco de Dados</h2>";

if ($dbHost === 'NÃO DEFINIDO' || $dbName === 'NÃO DEFINIDO' || $dbUser === 'NÃO DEFINIDO') {
    echo "❌ <strong>ERRO: Variáveis de ambiente não configuradas!</strong><br>";
    echo "<br><strong>SOLUÇÃO:</strong><br>";
    echo "1. Criar arquivo .env na raiz do projeto<br>";
    echo "2. Adicionar as credenciais do banco de dados<br>";
} else {
    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $dbHost,
            $dbPort,
            $dbName
        );
        
        echo "🔗 Tentando conectar...<br>";
        echo "📝 DSN: " . htmlspecialchars($dsn) . "<br>";
        
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        echo "<br><h3 style='color: green;'>✅ CONEXÃO ESTABELECIDA COM SUCESSO!</h3><br>";
        
        // Verificar se tabelas existem
        echo "<h3>4. Verificando Tabelas</h3>";
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "✅ Banco contém " . count($tables) . " tabelas:<br>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>" . htmlspecialchars($table) . "</li>";
            }
            echo "</ul>";
            
            // Verificar tabela users
            if (in_array('users', $tables)) {
                echo "<br>✅ Tabela 'users' encontrada!<br>";
                echo "<br><h3 style='color: green;'>🎉 TUDO PRONTO! O sistema pode criar contas.</h3>";
            } else {
                echo "<br>❌ Tabela 'users' NÃO encontrada!<br>";
                echo "<strong>SOLUÇÃO: Importar o arquivo sql/schema.sql no phpMyAdmin</strong><br>";
            }
        } else {
            echo "❌ Banco de dados está VAZIO<br>";
            echo "<strong>SOLUÇÃO: Importar o arquivo sql/schema.sql no phpMyAdmin</strong><br>";
        }
        
    } catch (PDOException $e) {
        echo "<br><h3 style='color: red;'>❌ ERRO AO CONECTAR:</h3>";
        echo "<pre style='background: #ffebee; padding: 15px; border-left: 4px solid red;'>";
        echo htmlspecialchars($e->getMessage());
        echo "</pre>";
        
        echo "<br><strong>POSSÍVEIS CAUSAS:</strong><br>";
        echo "<ul>";
        echo "<li>❌ Credenciais incorretas (usuário/senha)</li>";
        echo "<li>❌ Banco de dados não existe</li>";
        echo "<li>❌ Host incorreto (deve ser 'localhost' na Hostinger)</li>";
        echo "<li>❌ Servidor MySQL não está rodando</li>";
        echo "</ul>";
    }
}

echo "<hr>";
echo "<h2>📋 Próximos Passos</h2>";
echo "<ol>";
echo "<li>Se o arquivo .env não existe: criar no File Manager da Hostinger</li>";
echo "<li>Se a conexão falhou: verificar credenciais no hPanel da Hostinger</li>";
echo "<li>Se o banco está vazio: importar sql/schema.sql no phpMyAdmin</li>";
echo "</ol>";

echo "<br><br>";
echo "<a href='/' style='display: inline-block; background: #10B981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Voltar para Home</a> ";
echo "<a href='/backend/register.php' style='display: inline-block; background: #3B82F6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Criar Conta →</a>";
?>

