<?php
require_once __DIR__ . '/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /backend/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    csrf_verify();
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Todos os campos são obrigatórios';
    } elseif ($password !== $password_confirm) {
        $error = 'As senhas não conferem';
    } elseif (strlen($password) < 6) {
        $error = 'A senha deve ter no mínimo 6 caracteres';
    } else {
        try {
            $db = getDB();
            
            // Check if email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email já cadastrado';
            } else {
                // Create user (without email verification initially)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, email_verified_at) VALUES (?, ?, ?, NULL)");
                
                if ($stmt->execute([$name, $email, $hashed_password])) {
                    $userId = $db->lastInsertId();
                    
                    // Generate email verification token
                    $token = generateEmailVerificationToken();
                    createEmailVerificationRecord($userId, $token);
                    
                    // Send verification email
                    $verificationUrl = getenv('APP_URL') . '/backend/verify-email.php?token=' . $token;
                    $emailBody = "
                        <h2>Bem-vindo ao VisionMetrics!</h2>
                        <p>Olá {$name},</p>
                        <p>Para ativar sua conta, clique no link abaixo:</p>
                        <p><a href='{$verificationUrl}' style='background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>Verificar Email</a></p>
                        <p>Se o botão não funcionar, copie e cole este link no seu navegador:</p>
                        <p>{$verificationUrl}</p>
                        <p>Este link expira em 24 horas.</p>
                    ";
                    
                    sendEmail($email, 'Verifique seu email - VisionMetrics', $emailBody);
                    
                    // Create default workspace
                    $slug = 'ws-' . uniqid();
                    $stmt = $db->prepare("INSERT INTO workspaces (name, slug, owner_id, plan, status) VALUES (?, ?, ?, 'pro', 'active')");
                    $stmt->execute(['Meu Workspace', $slug, $userId]);
                    $workspaceId = $db->lastInsertId();
                    
                    // Add user as owner
                    $stmt = $db->prepare("INSERT INTO workspace_members (workspace_id, user_id, role) VALUES (?, ?, 'owner')");
                    $stmt->execute([$workspaceId, $userId]);
                    
                    $success = 'Cadastro realizado com sucesso! Verifique seu email para ativar a conta.';
                } else {
                    $error = 'Erro ao cadastrar. Tente novamente.';
                }
            }
        } catch (Exception $e) {
            $error = 'Erro ao conectar com o banco de dados: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - VisionMetrics</title>
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
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
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
                <p>Lead Tracking & WhatsApp Attribution Platform</p>
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
                    <label for="name">Nome Completo</label>
                    <input type="text" id="name" name="name" required autofocus value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Seu nome">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="seu@email.com">
                </div>

                <div class="form-group">
                    <label for="password">Senha (mínimo 6 caracteres)</label>
                    <input type="password" id="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres">
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirmar Senha</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="6" placeholder="Repita a senha">
                </div>

                <button type="submit">Criar Conta Grátis</button>
            </form>

            <div class="auth-footer">
                Já tem uma conta? <a href="/backend/login.php">Fazer Login</a>
            </div>
        </div>
    </div>
</body>
</html>
