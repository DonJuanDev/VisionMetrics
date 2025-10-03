<?php
session_start();

// Se já está logado, redirecionar para dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /backend/dashboard.php');
    exit;
}

// Processar login
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        try {
            // Conectar ao banco
            $dsn = 'mysql:host=mysql;port=3306;dbname=visionmetrics;charset=utf8mb4';
            $pdo = new PDO($dsn, 'visionmetrics', 'visionmetrics', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            // Buscar usuário
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login bem-sucedido
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                
                // Buscar workspace
                $stmt = $pdo->prepare("
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
                $error = 'Email ou senha inválidos';
            }
            
        } catch (Exception $e) {
            $error = 'Erro ao fazer login';
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
        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 48px;
            width: 100%;
            max-width: 440px;
        }
        .logo h1 {
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 32px;
        }
        .form-group {
            margin-bottom: 24px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #334155;
        }
        input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        .demo {
            margin-top: 24px;
            padding: 16px;
            background: #f1f5f9;
            border-radius: 8px;
            font-size: 13px;
        }
        .demo code {
            background: white;
            padding: 2px 6px;
            border-radius: 4px;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>VisionMetrics</h1>
        </div>
        
        <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
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
        
        <div class="demo">
            <strong>🔑 Credenciais Admin:</strong><br>
            Email: <code>admin@visionmetrics.com</code><br>
            Senha: <code>password</code>
        </div>
        
        <div style="margin-top: 20px; text-align: center; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.2);">
            <p style="color: white; font-size: 14px;">
                Não tem uma conta? 
                <a href="/backend/register.php" style="color: #fff; font-weight: 600; text-decoration: underline;">
                    Criar conta grátis
                </a>
            </p>
        </div>
    </div>
</body>
</html>