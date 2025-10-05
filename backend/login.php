<?php
require_once __DIR__ . '/config.php';

// Se já está logado, redirecionar para dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /backend/dashboard.php');
    exit;
}

// Processar login
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    csrf_verify();
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        // Check rate limiting
        $identifier = $email . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        if (!checkRateLimit($identifier)) {
            $error = 'Muitas tentativas de login. Tente novamente em 15 minutos.';
        } else {
            try {
                $db = getDB();
                
                // Buscar usuário
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    // Login bem-sucedido
                    recordLoginAttempt($identifier, true);
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['name'];
                    
                    // Buscar workspace
                    $stmt = $db->prepare("
                        SELECT w.* FROM workspaces w
                        INNER JOIN workspace_members wm ON w.id = wm.workspace_id
                        WHERE wm.user_id = ?
                        LIMIT 1
                    ");
                    $stmt->execute([$user['id']]);
                    $workspace = $stmt->fetch();
                    
                    if ($workspace) {
                        $_SESSION['workspace_id'] = $workspace['id'];
                    }
                    
                    header('Location: /backend/dashboard.php');
                    exit;
                } else {
                    recordLoginAttempt($identifier, false);
                    $error = 'Email ou senha inválidos';
                }
                
            } catch (Exception $e) {
                recordLoginAttempt($identifier, false);
                $error = 'Erro ao fazer login';
            }
        }
    } else {
        $error = 'Preencha email e senha';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VisionMetrics</title>
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

        .login-container {
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-glass);
            padding: 48px;
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo h1 {
            font-size: 36px;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .logo p {
            color: var(--text-secondary);
            font-size: 16px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
        }

        input {
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

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
            background: var(--bg-glass-hover);
        }

        .btn-login {
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

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(139, 92, 246, 0.4);
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #FCA5A5;
            padding: 16px 20px;
            border-radius: var(--radius-lg);
            margin-bottom: 24px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .demo {
            margin-top: 24px;
            padding: 20px;
            background: var(--bg-glass);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            font-size: 13px;
            color: var(--text-secondary);
        }

        .demo code {
            background: rgba(139, 92, 246, 0.2);
            padding: 4px 8px;
            border-radius: var(--radius);
            color: var(--primary);
            font-weight: 600;
        }

        .auth-links {
            margin-top: 24px;
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .auth-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition-normal);
        }

        .auth-links a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        .auth-links p {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 8px;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .login-container {
                padding: 32px 24px;
                margin: 10px;
            }
            
            .logo h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <div style="width: 48px; height: 48px; background: var(--gradient-primary); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; margin-right: 16px; box-shadow: 0 8px 32px rgba(139, 92, 246, 0.3);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M4 4L4 8L8 12L4 16L4 20L12 12L4 4Z" fill="white"/>
                        <path d="M20 4L20 8L16 12L20 16L20 20L12 12L20 4Z" fill="#A78BFA"/>
                    </svg>
                </div>
            </div>
            <h1>VisionMetrics</h1>
            <p>Gerencie seus leads com inteligência</p>
        </div>
        
        <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus placeholder="seu@email.com">
            </div>
            
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="password" required placeholder="Sua senha">
            </div>
            
            <button type="submit" class="btn-login">Entrar no Sistema</button>
        </form>
        
        <div class="auth-links">
            <a href="/backend/password-reset-request.php">
                Esqueceu sua senha?
            </a>
        </div>
        
        <div class="demo">
            <strong>🔑 Credenciais Admin:</strong><br>
            Email: <code>admin@visionmetrics.com</code><br>
            Senha: <code>password</code>
        </div>
        
        <div class="auth-links">
            <p>Não tem uma conta?</p>
            <a href="/backend/register.php">Criar conta grátis</a>
        </div>
    </div>
</body>
</html>