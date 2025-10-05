<?php
require_once __DIR__ . '/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /backend/dashboard/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    csrf_verify();
    
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Digite seu email';
    } else {
        try {
            $db = getDB();
            
            // Check if user exists
            $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $token = generatePasswordResetToken();
                createPasswordResetRecord($email, $token);
                
                // Send reset email
                $resetUrl = getenv('APP_URL') . '/backend/password-reset.php?token=' . $token;
                $emailBody = "
                    <h2>Redefinir Senha - VisionMetrics</h2>
                    <p>Olá {$user['name']},</p>
                    <p>Você solicitou a redefinição de sua senha. Clique no link abaixo para criar uma nova senha:</p>
                    <p><a href='{$resetUrl}' style='background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>Redefinir Senha</a></p>
                    <p>Se o botão não funcionar, copie e cole este link no seu navegador:</p>
                    <p>{$resetUrl}</p>
                    <p>Este link expira em 1 hora.</p>
                    <p>Se você não solicitou esta redefinição, ignore este email.</p>
                ";
                
                sendEmail($email, 'Redefinir Senha - VisionMetrics', $emailBody);
            }
            
            // Always show success message (security: don't reveal if email exists)
            $success = 'Se o email estiver cadastrado, você receberá um link para redefinir sua senha.';
            
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
    <title>Esqueci Minha Senha - VisionMetrics</title>
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
        input[type="email"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }
        input[type="email"]:focus {
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
                <p>Redefinir Senha</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="seu@email.com">
                </div>

                <button type="submit">Enviar Link de Redefinição</button>
            </form>

            <div class="auth-footer">
                Lembrou da senha? <a href="/backend/login.php">Fazer Login</a>
            </div>
        </div>
    </div>
</body>
</html>
