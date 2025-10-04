<?php
require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../integrations/stripe.php';

use VisionMetrics\Integrations\StripeHandler;

$db = getDB();
$stripe = new StripeHandler();

// Get current subscription
$stmt = $db->prepare("
    SELECT s.*, p.plan, p.amount, p.currency 
    FROM subscriptions s 
    LEFT JOIN payments p ON s.id = p.subscription_id 
    WHERE s.workspace_id = ? 
    ORDER BY s.created_at DESC 
    LIMIT 1
");
$stmt->execute([$currentWorkspace['id']]);
$currentSubscription = $stmt->fetch();

// Get pricing plans
$pricingPlans = $stripe->getPricingPlans();

// Handle subscription changes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'subscribe') {
        $plan = $_POST['plan'] ?? '';
        
        if (isset($pricingPlans[$plan])) {
            // Create or update subscription
            $result = $stripe->createSubscription(
                $currentUser['id'], // Using user ID as customer ID for simplicity
                $plan,
                ['workspace_id' => $currentWorkspace['id']]
            );
            
            if ($result['success']) {
                $_SESSION['success'] = 'Assinatura criada com sucesso!';
            } else {
                $_SESSION['error'] = 'Erro ao criar assinatura: ' . $result['error'];
            }
        }
    } elseif ($action === 'cancel') {
        // Cancel subscription
        if ($currentSubscription) {
            $stmt = $db->prepare("
                UPDATE subscriptions 
                SET status = 'cancelled', cancelled_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$currentSubscription['id']]);
            $_SESSION['success'] = 'Assinatura cancelada com sucesso!';
        }
    }
    
    redirect('/backend/billing.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing - VisionMetrics</title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1>💳 Billing & Assinaturas</h1>
            <p>Gerencie sua assinatura e pagamentos</p>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <!-- Current Subscription -->
        <?php if ($currentSubscription): ?>
        <div class="card">
            <div class="card-header">
                <h2>Assinatura Atual</h2>
            </div>
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h3 style="margin: 0; color: #1F2937;">Plano <?= ucfirst($currentSubscription['plan']) ?></h3>
                        <p style="margin: 4px 0 0; color: #6B7280;">
                            $<?= number_format($currentSubscription['amount'], 2) ?>/mês
                        </p>
                    </div>
                    <div>
                        <span class="badge badge-<?= $currentSubscription['status'] === 'active' ? 'success' : 'warning' ?>">
                            <?= ucfirst($currentSubscription['status']) ?>
                        </span>
                    </div>
                </div>
                
                <?php if ($currentSubscription['status'] === 'active'): ?>
                    <form method="POST" style="display: inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="cancel">
                        <button type="submit" class="btn btn-secondary" 
                                onclick="return confirm('Tem certeza que deseja cancelar sua assinatura?')">
                            Cancelar Assinatura
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Pricing Plans -->
        <div class="card">
            <div class="card-header">
                <h2>Planos Disponíveis</h2>
            </div>
            <div class="card-body">
                <div class="pricing-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                    <?php foreach ($pricingPlans as $planKey => $plan): ?>
                        <div class="pricing-card" style="border: 2px solid <?= $planKey === 'pro' ? '#3B82F6' : '#E5E7EB' ?>; border-radius: 12px; padding: 24px; text-align: center; position: relative;">
                            <?php if ($planKey === 'pro'): ?>
                                <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #3B82F6; color: white; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                    MAIS POPULAR
                                </div>
                            <?php endif; ?>
                            
                            <h3 style="margin: 0 0 8px; color: #1F2937;"><?= $plan['name'] ?></h3>
                            <div style="margin-bottom: 16px;">
                                <span style="font-size: 36px; font-weight: 700; color: #1F2937;">$<?= $plan['price'] ?></span>
                                <span style="color: #6B7280;">/<?= $plan['interval'] ?></span>
                            </div>
                            
                            <ul style="list-style: none; padding: 0; margin: 0 0 24px; text-align: left;">
                                <?php foreach ($plan['features'] as $feature): ?>
                                    <li style="padding: 8px 0; display: flex; align-items: center; gap: 8px;">
                                        <span style="color: #10B981;">✓</span>
                                        <span><?= htmlspecialchars($feature) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <?php if ($currentSubscription && $currentSubscription['plan'] === $planKey): ?>
                                <button class="btn btn-secondary" disabled>Plano Atual</button>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="subscribe">
                                    <input type="hidden" name="plan" value="<?= $planKey ?>">
                                    <button type="submit" class="btn btn-<?= $planKey === 'pro' ? 'primary' : 'secondary' ?>">
                                        <?= $plan['price'] > 0 ? 'Assinar' : 'Ativar Grátis' ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Payment History -->
        <div class="card">
            <div class="card-header">
                <h2>Histórico de Pagamentos</h2>
            </div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("
                    SELECT p.*, s.plan 
                    FROM payments p 
                    LEFT JOIN subscriptions s ON p.subscription_id = s.id 
                    WHERE p.workspace_id = ? 
                    ORDER BY p.created_at DESC 
                    LIMIT 10
                ");
                $stmt->execute([$currentWorkspace['id']]);
                $payments = $stmt->fetchAll();
                ?>
                
                <?php if (empty($payments)): ?>
                    <p style="text-align: center; color: #6B7280; padding: 40px;">
                        Nenhum pagamento encontrado
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Plano</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>ID do Pagamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($payment['created_at'])) ?></td>
                                        <td><?= ucfirst($payment['plan'] ?? 'N/A') ?></td>
                                        <td>$<?= number_format($payment['amount'], 2) ?> <?= strtoupper($payment['currency']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $payment['status'] === 'succeeded' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($payment['status']) ?>
                                            </span>
                                        </td>
                                        <td style="font-family: monospace; font-size: 12px;">
                                            <?= htmlspecialchars($payment['stripe_payment_id']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
