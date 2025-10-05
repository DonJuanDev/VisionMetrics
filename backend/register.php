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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/frontend/css/style.css">
    <style>
        :root {
            --bg-primary: #0A0A0B;
            --bg-secondary: #111113;
            --bg-glass: rgba(255, 255, 255, 0.05);
            --bg-glass-hover: rgba(255, 255, 255, 0.1);
            --text-primary: #FFFFFF;
            --text-secondary: #A1A1AA;
            --text-muted: #71717A;
            --primary: #8B5CF6;
            --secondary: #3B82F6;
            --accent: #10B981;
            --gradient-primary: linear-gradient(135deg, #8B5CF6 0%, #3B82F6 100%);
            --gradient-secondary: linear-gradient(135deg, #3B82F6 0%, #10B981 100%);
            --gradient-accent: linear-gradient(135deg, #10B981 0%, #F59E0B 100%);
            --gradient-glass: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            --shadow-glass: 0 8px 32px rgba(0, 0, 0, 0.3);
            --shadow-glass-hover: 0 12px 40px rgba(0, 0, 0, 0.4);
            --radius: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --transition-normal: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Background Animation */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(139, 92, 246, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(59, 130, 246, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(16, 185, 129, 0.2) 0%, transparent 50%);
            z-index: -1;
            animation: backgroundShift 20s ease-in-out infinite;
        }

        @keyframes backgroundShift {
            0%, 100% { transform: translateX(0) translateY(0) scale(1); }
            33% { transform: translateX(-20px) translateY(-20px) scale(1.1); }
            66% { transform: translateX(20px) translateY(20px) scale(0.9); }
        }

        .auth-container {
            width: 100%;
            max-width: 480px;
        }

        .auth-box {
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-glass);
            padding: 48px;
            position: relative;
            z-index: 1;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .auth-logo h1 {
            font-size: 36px;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .auth-logo p {
            color: var(--text-secondary);
            font-size: 16px;
            font-weight: 400;
        }

        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-lg);
            margin-bottom: 24px;
            font-size: 14px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #FCA5A5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6EE7B7;
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 16px 20px;
            background: var(--bg-glass);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            font-size: 15px;
            color: var(--text-primary);
            transition: var(--transition-normal);
        }

        input::placeholder {
            color: var(--text-muted);
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
            background: var(--bg-glass-hover);
        }

        button[type="submit"] {
            width: 100%;
            padding: 16px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: var(--radius-lg);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        button[type="submit"]::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        button[type="submit"]:hover::before {
            left: 100%;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(139, 92, 246, 0.4);
        }

        .auth-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 14px;
            color: var(--text-secondary);
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition-normal);
        }

        .auth-footer a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .auth-box {
                padding: 32px 24px;
                margin: 10px;
            }
            
            .auth-logo h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                    <div style="width: 48px; height: 48px; background: var(--gradient-primary); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin-right: 16px; box-shadow: 0 8px 32px rgba(139, 92, 246, 0.3);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M4 4L4 8L8 12L4 16L4 20L12 12L4 4Z" fill="white"/>
                            <path d="M20 4L20 8L16 12L20 16L20 20L12 12L20 4Z" fill="#A78BFA"/>
                        </svg>
                    </div>
                </div>
                <h1>VisionMetrics</h1>
                <p>Comece sua jornada de sucesso</p>
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
