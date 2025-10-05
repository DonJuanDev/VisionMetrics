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
                // Create user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, email_verified_at) VALUES (?, ?, ?, NOW())");
                
                if ($stmt->execute([$name, $email, $hashed_password])) {
                    $userId = $db->lastInsertId();
                    
                    // Create default workspace
                    $slug = 'ws-' . uniqid();
                    $stmt = $db->prepare("INSERT INTO workspaces (name, slug, owner_id, plan, status) VALUES (?, ?, ?, 'pro', 'active')");
                    $stmt->execute(['Meu Workspace', $slug, $userId]);
                    $workspaceId = $db->lastInsertId();
                    
                    // Add user as owner
                    $stmt = $db->prepare("INSERT INTO workspace_members (workspace_id, user_id, role) VALUES (?, ?, 'owner')");
                    $stmt->execute([$workspaceId, $userId]);
                    
                    // Auto login
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['workspace_id'] = $workspaceId;
                    
                    header('Location: /backend/dashboard.php');
                    exit;
                } else {
                    $error = 'Erro ao cadastrar. Tente novamente.';
                }
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
    <title>Criar Conta - VisionMetrics</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --gradient-primary: linear-gradient(135deg, #8B5CF6 0%, #3B82F6 50%, #10B981 100%);
            --gradient-secondary: linear-gradient(135deg, #10B981 0%, #3B82F6 100%);
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
            background: var(--gradient-secondary);
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
                radial-gradient(circle at 80% 30%, rgba(139, 92, 246, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 20% 70%, rgba(59, 130, 246, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 60% 50%, rgba(16, 185, 129, 0.3) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(-1deg); }
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
            overflow-y: auto;
            overflow-x: hidden;
        }

        .right::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
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
            margin-bottom: 40px;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .auth-title {
            font-size: 42px;
            font-weight: 800;
            background: var(--gradient-secondary);
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

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6EE7B7;
        }

        .form-group {
            margin-bottom: 20px;
            animation: fadeInUp 0.8s ease-out calc(0.3s + var(--delay, 0s)) both;
        }

        .form-group:nth-child(1) { --delay: 0.1s; }
        .form-group:nth-child(2) { --delay: 0.2s; }
        .form-group:nth-child(3) { --delay: 0.3s; }
        .form-group:nth-child(4) { --delay: 0.4s; }

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
            border-color: #10B981;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
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
            background: var(--gradient-secondary);
            color: white;
            margin-bottom: 16px;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
            animation: fadeInUp 0.8s ease-out 0.8s both;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(16, 185, 129, 0.5);
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
            animation: fadeInUp 0.8s ease-out 0.9s both;
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
            font-size: 13px;
            animation: fadeInUp 0.8s ease-out 1s both;
            line-height: 1.6;
        }

        .auth-footer a {
            color: #10B981;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .auth-footer a:hover {
            color: #34D399;
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

        /* Password Strength Indicator */
        .password-strength {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .password-strength-bar.weak {
            width: 33%;
            background: #EF4444;
        }

        .password-strength-bar.medium {
            width: 66%;
            background: #F59E0B;
        }

        .password-strength-bar.strong {
            width: 100%;
            background: #10B981;
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
                <p>Junte-se a milhares de empresas que já transformam leads em clientes</p>
            </div>
            
            <!-- Floating Particles -->
            <div class="particle" style="left: 15%; animation-delay: 1s;"></div>
            <div class="particle" style="left: 35%; animation-delay: 4s;"></div>
            <div class="particle" style="left: 55%; animation-delay: 7s;"></div>
            <div class="particle" style="left: 75%; animation-delay: 10s;"></div>
            <div class="particle" style="left: 85%; animation-delay: 13s;"></div>
        </div>

        <div class="right">
            <div class="auth-form">
                <div class="auth-header">
                    <h1 class="auth-title">Criar Conta</h1>
                    <p class="auth-subtitle">Comece grátis por 30 dias 🚀</p>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-error">
                    <strong>⚠️ Erro:</strong> <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>✅ Sucesso:</strong> <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">👤 Nome Completo</label>
                        <input type="text" name="name" class="form-input" required autofocus value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Seu nome completo">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">📧 Email</label>
                        <input type="email" name="email" class="form-input" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="seu@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">🔒 Senha</label>
                        <input type="password" name="password" class="form-input" required minlength="6" placeholder="Mínimo 6 caracteres" id="password">
                        <div class="password-strength">
                            <div class="password-strength-bar" id="strength-bar"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">🔐 Confirme a Senha</label>
                        <input type="password" name="password_confirm" class="form-input" required minlength="6" placeholder="Digite a senha novamente">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Criar Minha Conta</button>
                    <a href="/backend/login.php" style="text-decoration: none;">
                        <button type="button" class="btn btn-secondary">Já Tenho Conta</button>
                    </a>
                </form>

                <div class="auth-footer">
                    <p>Ao criar uma conta, você concorda com nossos <a href="#">Termos de Uso</a> e <a href="#">Política de Privacidade</a></p>
                    <p style="margin-top: 16px;"><a href="/index.php">← Voltar para Home</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strength-bar');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            
            strengthBar.className = 'password-strength-bar';
            if (strength === 'weak') {
                strengthBar.classList.add('weak');
            } else if (strength === 'medium') {
                strengthBar.classList.add('medium');
            } else if (strength === 'strong') {
                strengthBar.classList.add('strong');
            }
        });

        function calculatePasswordStrength(password) {
            if (password.length === 0) return '';
            if (password.length < 6) return 'weak';
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;
            
            if (strength <= 1) return 'weak';
            if (strength <= 2) return 'medium';
            return 'strong';
        }
    </script>
</body>
</html>
