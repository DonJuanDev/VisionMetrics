<?php
require_once __DIR__ . '/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /backend/dashboard/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email e senha são obrigatórios';
    } else {
        try {
            $db = getDB();
            
            $stmt = $db->prepare("SELECT id, password_hash FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                
                // Get user's workspace
                $stmt = $db->prepare("
                    SELECT w.id 
                    FROM workspaces w
                    JOIN workspace_members wm ON w.id = wm.workspace_id
                    WHERE wm.user_id = ?
                    LIMIT 1
                ");
                $stmt->execute([$user['id']]);
                $workspace = $stmt->fetch();
                
                if ($workspace) {
                    $_SESSION['workspace_id'] = $workspace['id'];
                }
                
                header('Location: /backend/dashboard/dashboard.php');
                exit;
            } else {
                $error = 'Email ou senha incorretos';
            }
        } catch (Exception $e) {
            $error = 'Erro: ' . $e->getMessage();
        }
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --gradient-primary: linear-gradient(135deg, #8B5CF6 0%, #3B82F6 50%, #10B981 100%);
            --gradient-secondary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --shadow-glow: 0 20px 60px rgba(139, 92, 246, 0.3);
            --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            background: #0A0A0B;
            overflow: hidden;
        }

        .container {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
        }

        /* Animated Background */
        .left {
            flex: 1;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(139, 92, 246, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(59, 130, 246, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(16, 185, 129, 0.3) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(1deg); }
        }

        /* Grid Pattern */
        .left::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 50px 50px;
            opacity: 0.3;
        }

        .left-content {
            text-align: center;
            position: relative;
            z-index: 2;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-circle {
            width: 120px;
            height: 120px;
            margin: 0 auto 24px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { 
                transform: scale(1);
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            }
            50% { 
                transform: scale(1.05);
                box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
            }
        }

        .left-content h1 {
            font-size: 56px;
            font-weight: 800;
            margin-bottom: 16px;
            font-family: 'Poppins', sans-serif;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .left-content p {
            font-size: 18px;
            opacity: 0.9;
            max-width: 400px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Right Side */
        .right {
            flex: 1;
            background: #0A0A0B;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }

        .right::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .auth-form {
            max-width: 420px;
            margin: 0 auto;
            width: 100%;
            position: relative;
            z-index: 2;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 48px;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .auth-title {
            font-size: 42px;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 12px;
            font-family: 'Poppins', sans-serif;
        }

        .auth-subtitle {
            font-size: 16px;
            color: #B0B0B0;
            font-weight: 500;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            animation: shake 0.5s ease-out;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #FCA5A5;
        }

        .form-group {
            margin-bottom: 24px;
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #FFFFFF;
            letter-spacing: 0.3px;
        }

        .form-input {
            width: 100%;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            color: #FFFFFF;
            font-weight: 500;
        }

        .form-input:focus {
            outline: none;
            border-color: #8B5CF6;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);
            transform: translateY(-2px);
        }

        .form-input::placeholder {
            color: #808080;
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            margin-bottom: 16px;
            box-shadow: 0 10px 30px rgba(139, 92, 246, 0.4);
            animation: fadeInUp 0.8s ease-out 0.6s both;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(139, 92, 246, 0.5);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.8s ease-out 0.7s both;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .auth-footer {
            text-align: center;
            margin-top: 32px;
            color: #B0B0B0;
            font-size: 14px;
            animation: fadeInUp 0.8s ease-out 0.8s both;
        }

        .auth-footer a {
            color: #8B5CF6;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .auth-footer a:hover {
            color: #A78BFA;
            text-decoration: underline;
        }

        /* Floating Particles */
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: floatParticle 20s linear infinite;
        }

        @keyframes floatParticle {
            0% {
                transform: translateY(100vh) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }

        @media (max-width: 968px) {
            body {
                overflow-y: auto;
            }
            
            .container {
                flex-direction: column;
                min-height: auto;
            }
            
            .left {
                min-height: 40vh;
                flex: none;
            }

            .left-content h1 {
                font-size: 36px;
            }
            
            .left-content p {
                font-size: 14px;
            }
            
            .right {
                padding: 40px 24px;
                min-height: 60vh;
                flex: none;
            }

            .auth-title {
                font-size: 32px;
            }
            
            .auth-header {
                margin-bottom: 32px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
        }

        @media (max-width: 480px) {
            .left {
                min-height: 30vh;
                padding: 24px;
            }
            
            .logo-circle {
                width: 80px;
                height: 80px;
                margin-bottom: 16px;
            }
            
            .logo-circle svg {
                width: 40px;
                height: 40px;
            }
            
            .left-content h1 {
                font-size: 28px;
            }
            
            .left-content p {
                font-size: 13px;
            }
            
            .right {
                padding: 32px 20px;
            }
            
            .auth-header {
                margin-bottom: 24px;
            }

            .auth-title {
                font-size: 24px;
            }
            
            .auth-subtitle {
                font-size: 14px;
            }

            .form-input {
                padding: 14px 16px;
                font-size: 15px;
            }
            
            .form-label {
                font-size: 13px;
            }
            
            .btn {
                padding: 14px 24px;
                font-size: 15px;
            }
            
            .auth-footer {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left">
            <div class="left-content">
                <div class="logo-circle">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none">
                        <path d="M4 4L4 8L8 12L4 16L4 20L12 12L4 4Z" fill="white"/>
                        <path d="M20 4L20 8L16 12L20 16L20 20L12 12L20 4Z" fill="rgba(255, 255, 255, 0.7)"/>
                    </svg>
                </div>
                <h1>VisionMetrics</h1>
                <p>A plataforma mais avançada para gestão de leads com inteligência artificial</p>
            </div>
            
            <!-- Floating Particles -->
            <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
            <div class="particle" style="left: 30%; animation-delay: 3s;"></div>
            <div class="particle" style="left: 50%; animation-delay: 6s;"></div>
            <div class="particle" style="left: 70%; animation-delay: 9s;"></div>
            <div class="particle" style="left: 90%; animation-delay: 12s;"></div>
        </div>

        <div class="right">
            <div class="auth-form">
                <div class="auth-header">
                    <h1 class="auth-title">Bem-vindo de volta!</h1>
                    <p class="auth-subtitle">Entre na sua conta para continuar</p>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-error">
                    <strong>⚠️ Erro:</strong> <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">📧 Email</label>
                        <input type="email" name="email" class="form-input" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="seu@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">🔒 Senha</label>
                        <input type="password" name="password" class="form-input" required placeholder="Digite sua senha">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Entrar Agora</button>
                    <a href="/backend/register.php" style="text-decoration: none;">
                        <button type="button" class="btn btn-secondary">Criar Nova Conta</button>
                    </a>
                </form>

                <div class="auth-footer">
                    <p><a href="/backend/password-reset-request.php">🔑 Esqueci minha senha</a></p>
                    <p style="margin-top: 16px;"><a href="/index.php">← Voltar para Home</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
