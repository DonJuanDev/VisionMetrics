<?php
require_once __DIR__ . '/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /backend/dashboard/dashboard.php');
    exit;
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

// Validate token
if (empty($token)) {
    $error = 'Token inválido ou expirado';
} else {
    $resetRecord = validatePasswordResetToken($token);
    if (!$resetRecord) {
        $error = 'Token inválido ou expirado';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    // Verify CSRF token
    csrf_verify();
    
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($password) || empty($password_confirm)) {
        $error = 'Todos os campos são obrigatórios';
    } elseif ($password !== $password_confirm) {
        $error = 'As senhas não conferem';
    } elseif (strlen($password) < 6) {
        $error = 'A senha deve ter no mínimo 6 caracteres';
    } else {
        try {
            $db = getDB();
            
            // Update password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE email = ?");
            
            if ($stmt->execute([$hashed_password, $resetRecord['email']])) {
                // Delete used token
                deletePasswordResetToken($token);
                
                $success = 'Senha redefinida com sucesso! Redirecionando para o login...';
                echo '<meta http-equiv="refresh" content="3;url=/backend/login.php">';
            } else {
                $error = 'Erro ao redefinir senha. Tente novamente.';
            }
            
        } catch (Exception $e) {
            $error = 'Erro ao processar solicitação. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Senha - VisionMetrics</title>
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
            font-size: 14px;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
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
                <p>Nova Senha</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if (!$error): ?>
            <form method="POST">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label for="password">Nova Senha (mínimo 6 caracteres)</label>
                    <input type="password" id="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres">
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirmar Nova Senha</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="6" placeholder="Repita a senha">
                </div>

                <button type="submit">Redefinir Senha</button>
            </form>
            <?php endif; ?>

            <div class="auth-footer">
                <a href="/backend/login.php">Voltar ao Login</a>
            </div>
        </div>
    </div>
</body>
</html>
