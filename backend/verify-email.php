<?php
require_once __DIR__ . '/config.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Token inválido';
} else {
    try {
        $user = validateEmailVerificationToken($token);
        
        if (!$user) {
            $error = 'Token inválido ou expirado';
        } else {
            // Verify email
            $db = getDB();
            $stmt = $db->prepare("UPDATE users SET email_verified_at = NOW() WHERE id = ?");
            
            if ($stmt->execute([$user['id']])) {
                // Delete verification token
                deleteEmailVerificationToken($token);
                
                $success = 'Email verificado com sucesso! Redirecionando para o login...';
                echo '<meta http-equiv="refresh" content="3;url=/backend/login.php">';
            } else {
                $error = 'Erro ao verificar email. Tente novamente.';
            }
        }
    } catch (Exception $e) {
        $error = 'Erro ao processar verificação. Tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Email - VisionMetrics</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .auth-container {
            width: 100%;
            max-width: 450px;
        }
        .auth-box {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .auth-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-logo h1 {
            font-size: 32px;
            color: #667eea;
            margin-bottom: 10px;
        }
        .auth-logo p {
            color: #6b7280;
            font-size: 14px;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .auth-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
        .auth-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <h1>VisionMetrics</h1>
                <p>Verificação de Email</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="auth-footer">
                <a href="/backend/login.php">Ir para Login</a>
            </div>
        </div>
    </div>
</body>
</html>
