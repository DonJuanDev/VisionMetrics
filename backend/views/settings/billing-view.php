<?php
// Get subscription
$stmt = $db->prepare("SELECT * FROM subscriptions WHERE workspace_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$currentWorkspace['id']]);
$subscription = $stmt->fetch();

// Get usage
$stmt = $db->prepare("SELECT COUNT(*) as count FROM leads WHERE workspace_id = ?");
$stmt->execute([$currentWorkspace['id']]);
$leadCount = $stmt->fetch()['count'];

$plans = [
    'free' => ['name' => 'Free', 'price' => 0, 'leads' => 100],
    'starter' => ['name' => 'Starter', 'price' => 97, 'leads' => 1000],
    'pro' => ['name' => 'Pro', 'price' => 297, 'leads' => 10000],
    'business' => ['name' => 'Business', 'price' => 797, 'leads' => -1]
];

$currentPlan = $currentWorkspace['plan'] ?? 'free';
?>

<div class="card">
    <div class="card-header">
        <h2>Plano Atual: <?= $plans[$currentPlan]['name'] ?></h2>
    </div>
    <div class="card-body">
        <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr);">
            <div>
                <div style="font-size: 32px; font-weight: 700; color: #6366F1;"><?= $leadCount ?></div>
                <div style="font-size: 12px; color: #6B7280;">Leads Usados</div>
                <small class="text-muted">Limite: <?= $plans[$currentPlan]['leads'] == -1 ? 'Ilimitado' : $plans[$currentPlan]['leads'] ?></small>
            </div>
            <div>
                <div style="font-size: 32px; font-weight: 700; color: #10B981;">
                    R$ <?= number_format($plans[$currentPlan]['price'], 2, ',', '.') ?>
                </div>
                <div style="font-size: 12px; color: #6B7280;">Mensalidade</div>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <?php foreach ($plans as $key => $plan): ?>
        <div class="card <?= $key === $currentPlan ? 'plan-current' : '' ?>">
            <div class="card-header">
                <h2><?= $plan['name'] ?></h2>
            </div>
            <div class="card-body" style="text-align: center;">
                <div style="font-size: 36px; font-weight: 700; color: #4F46E5; margin: 20px 0;">
                    R$ <?= number_format($plan['price'], 0) ?>
                </div>
                <p class="text-muted">/mês</p>
                <ul style="list-style: none; padding: 20px 0; text-align: left;">
                    <li style="padding: 8px 0;">✓ <?= $plan['leads'] == -1 ? 'Leads ilimitados' : $plan['leads'] . ' leads' ?></li>
                    <li style="padding: 8px 0;">✓ Dashboard em tempo real</li>
                    <li style="padding: 8px 0;">✓ Integrações Meta & GA4</li>
                </ul>
                <?php if ($key !== $currentPlan): ?>
                    <button class="btn btn-primary btn-block">
                        <?= $plan['price'] > $plans[$currentPlan]['price'] ? 'Upgrade' : 'Downgrade' ?>
                    </button>
                <?php else: ?>
                    <div class="badge badge-success" style="padding: 10px; display: block;">Plano Atual</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .plan-current { border: 2px solid #4F46E5; }
</style>





