<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Atualizar .env</title>
    <style>
        body { font-family: Arial; padding: 30px; background: #1a1a1a; color: white; }
        .box { background: #2a2a2a; padding: 20px; margin: 20px 0; border-radius: 10px; }
        .success { background: #1a3a1a; border: 2px solid #10B981; }
        .error { background: #3a1a1a; border: 2px solid #ef4444; }
        h1 { color: #10B981; }
        pre { background: #1a1a1a; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .btn { background: #10B981; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; font-weight: bold; border: none; cursor: pointer; font-size: 16px; }
    </style>
</head>
<body>

<h1>🔧 Atualizar Arquivo .env</h1>

<?php
$envContent = 'APP_NAME=VisionMetrics
APP_ENV=production
APP_URL=https://visionmetricsapp.com.br
APP_DEBUG=true

DB_HOST=localhost
DB_NAME=u604248417_visionmetrics
DB_USER=u604248417_visionmetrics
DB_PASS=182876JJj?
DB_PORT=3306

REDIS_HOST=
REDIS_PORT=
REDIS_ENABLED=false

SESSION_LIFETIME=7200
SESSION_SECURE=true
SESSION_NAME=visionmetrics_session

JWT_SECRET=kN8mP2vR4xT6wY9zB1cD3fG5hJ7lM0nQ2sU4vX6yA8bC0dE2fH4jK6mN8pR0tV2w
CSRF_TOKEN_SALT=aB3dE5gH7jK9mN1qR3tV5xZ7cF9hL1nP3sU5wY7zA9bD1fG3jM5pR7tW9xC1eH3k

META_ADS_ACCESS_TOKEN=
META_ADS_PIXEL_ID=
GA4_MEASUREMENT_ID=
GA4_API_SECRET=
TIKTOK_PIXEL_ID=
TIKTOK_ACCESS_TOKEN=

STRIPE_PUBLIC_KEY=
STRIPE_SECRET_KEY=
STRIPE_WEBHOOK_SECRET=

SMTP_HOST=smtp.hostinger.com
SMTP_PORT=587
SMTP_USER=contato@visionmetricsapp.com.br
SMTP_PASS=
SMTP_FROM_EMAIL=contato@visionmetricsapp.com.br
SMTP_FROM_NAME=VisionMetrics

FEATURE_REAL_TIME=true
FEATURE_WORKFLOWS=true
FEATURE_CUSTOM_FIELDS=true

RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX_REQUESTS=100
RATE_LIMIT_WINDOW=60

LOG_LEVEL=INFO
LOG_CHANNEL=daily
LOG_PATH=./logs

ADAPTER_MODE=simulate
REQUIRE_EMAIL_VERIFICATION=false
SENTRY_DSN=

MAIL_FROM=contato@visionmetricsapp.com.br';

if (isset($_POST['atualizar'])) {
    $envPath = __DIR__ . '/.env';
    
    if (file_put_contents($envPath, $envContent)) {
        echo "<div class='box success'>";
        echo "<h2>✅ ARQUIVO .ENV ATUALIZADO COM SUCESSO!</h2>";
        echo "<p>Credenciais corretas foram salvas em: <code>$envPath</code></p>";
        echo "<p><strong>Agora tente criar sua conta!</strong></p>";
        echo "<br>";
        echo "<a href='/backend/register.php' class='btn' style='font-size: 20px;'>CRIAR CONTA AGORA →</a>";
        echo "</div>";
    } else {
        echo "<div class='box error'>";
        echo "<h2>❌ ERRO: Não foi possível escrever o arquivo!</h2>";
        echo "<p>Possíveis causas:</p>";
        echo "<ul>";
        echo "<li>Permissões de escrita insuficientes</li>";
        echo "<li>Diretório protegido</li>";
        echo "</ul>";
        echo "<p><strong>SOLUÇÃO MANUAL:</strong></p>";
        echo "<ol>";
        echo "<li>Abra o File Manager da Hostinger</li>";
        echo "<li>Delete o arquivo .env atual</li>";
        echo "<li>Crie um novo arquivo chamado .env</li>";
        echo "<li>Cole o conteúdo abaixo:</li>";
        echo "</ol>";
        echo "</div>";
    }
} else {
    echo "<div class='box'>";
    echo "<h2>📋 Conteúdo do Novo .env</h2>";
    echo "<p>Este arquivo vai SUBSTITUIR o .env atual com as credenciais corretas:</p>";
    echo "<pre>$envContent</pre>";
    echo "<br>";
    echo "<form method='POST'>";
    echo "<button type='submit' name='atualizar' class='btn' style='font-size: 20px;'>🔥 ATUALIZAR .ENV AGORA</button>";
    echo "</form>";
    echo "</div>";
}

// Verificar arquivo atual
echo "<div class='box'>";
echo "<h2>📄 Verificando .env Atual</h2>";

$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "<p>✅ Arquivo .env existe</p>";
    $currentContent = file_get_contents($envPath);
    
    // Extrair credenciais
    preg_match('/DB_NAME=(.+)/', $currentContent, $dbname);
    preg_match('/DB_USER=(.+)/', $currentContent, $dbuser);
    
    echo "<p><strong>Credenciais atuais no .env:</strong></p>";
    echo "<pre>";
    echo "DB_NAME=" . (isset($dbname[1]) ? trim($dbname[1]) : 'NÃO ENCONTRADO') . "\n";
    echo "DB_USER=" . (isset($dbuser[1]) ? trim($dbuser[1]) : 'NÃO ENCONTRADO') . "\n";
    echo "</pre>";
    
    if (isset($dbname[1]) && trim($dbname[1]) === 'u604248417_visionmetrics') {
        echo "<p style='color: #10B981;'>✅ Credenciais estão CORRETAS!</p>";
    } else {
        echo "<p style='color: #ef4444;'>❌ Credenciais estão ERRADAS! Clique em ATUALIZAR acima.</p>";
    }
} else {
    echo "<p style='color: #ef4444;'>❌ Arquivo .env NÃO existe!</p>";
}
echo "</div>";
?>

</body>
</html>


