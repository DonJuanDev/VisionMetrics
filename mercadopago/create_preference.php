<?php
/**
 * MercadoPago - Criar Preference (Checkout)
 * Endpoint para gerar link de pagamento
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth_check.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/adapters/MercadoPagoAdapter.php';

use VisionMetrics\Adapters\MercadoPagoAdapter;

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// CSRF check (descomente para ativar)
// csrf_verify();

$planId = $_POST['plan_id'] ?? null;

if (!$planId) {
    json_response(['error' => 'Plan ID required'], 400);
}

// Definir planos
$plans = [
    'starter' => ['name' => 'Starter', 'price' => 97.00],
    'pro' => ['name' => 'Professional', 'price' => 297.00],
    'business' => ['name' => 'Business', 'price' => 797.00]
];

if (!isset($plans[$planId])) {
    json_response(['error' => 'Invalid plan'], 400);
}

$plan = $plans[$planId];

// Criar preference
$mercadopago = new MercadoPagoAdapter();

$items = [[
    'title' => 'VisionMetrics - Plano ' . $plan['name'],
    'description' => 'Assinatura mensal do plano ' . $plan['name'],
    'quantity' => 1,
    'unit_price' => $plan['price'],
    'currency_id' => 'BRL'
]];

$metadata = [
    'workspace_id' => $currentWorkspace['id'],
    'user_id' => $currentUser['id'],
    'plan_id' => $planId
];

$result = $mercadopago->createPreference($items, $metadata);

if (!$result['success']) {
    json_response(['error' => $result['error'] ?? 'Failed to create preference'], 500);
}

// Salvar preference no banco para tracking
$db = getDB();
$stmt = $db->prepare("
    INSERT INTO subscriptions (workspace_id, plan, status, mercadopago_preference_id, amount, currency)
    VALUES (?, ?, 'pending', ?, ?, 'BRL')
");
$stmt->execute([
    $currentWorkspace['id'],
    $planId,
    $result['preference_id'],
    $plan['price']
]);

json_response([
    'success' => true,
    'preference_id' => $result['preference_id'],
    'init_point' => $result['init_point'] ?? $result['sandbox_init_point'],
    'mode' => env('ADAPTER_MODE')
]);



