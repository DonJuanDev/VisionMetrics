<?php
require_once __DIR__ . '/middleware.php';

$db = getDB();

// Get conversion events configured
$conversionEvents = [
    ['name' => 'Comprador', 'platform' => 'Meta Ads', 'icon' => '💰'],
    ['name' => 'Lead Qualificado', 'platform' => 'Google Ads', 'icon' => '⭐'],
    ['name' => 'ViewContent', 'platform' => 'Meta Ads', 'icon' => '👁️'],
    ['name' => 'Lead', 'platform' => 'Meta Ads', 'icon' => '📝'],
    ['name' => 'Purchase', 'platform' => 'Meta Ads', 'icon' => '🛒'],
    ['name' => 'AddPaymentInfo', 'platform' => 'Meta Ads', 'icon' => '💳'],
    ['name' => 'AddToCart', 'platform' => 'Meta Ads', 'icon' => '🛍️'],
    ['name' => 'AddToWishlist', 'platform' => 'Meta Ads', 'icon' => '❤️'],
    ['name' => 'CompleteRegistration', 'platform' => 'Meta Ads', 'icon' => '✅'],
    ['name' => 'Contact', 'platform' => 'Meta Ads', 'icon' => '📞']
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos de Conversão - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Eventos e Conversões</h1>
                    <p>Configure eventos para rastreamento de conversões</p>
                </div>
                <div class="top-bar-actions">
                    <button class="btn btn-primary">Adicionar Novo Evento</button>
                </div>
            </div>

            <div class="container">
                <div class="card" style="background: #EFF6FF; border-left: 4px solid #3B82F6; margin-bottom: 20px;">
                    <div class="card-body">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <svg width="20" height="20" fill="#3B82F6" viewBox="0 0 24 24">
                                <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <strong style="color: #1E40AF;">Dúvidas sobre Eventos de Conversão?</strong>
                                <p style="margin: 4px 0 0; font-size: 14px; color: #1E40AF;">
                                    🎥 <a href="#" style="color: #3B82F6; font-weight: 600;">Saiba mais no vídeo</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Plataforma</th>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Criado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($conversionEvents as $event): ?>
                                    <tr>
                                        <td>
                                            <?php if ($event['platform'] === 'Meta Ads'): ?>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#3B82F6">
                                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                                                    </svg>
                                                    <span style="font-weight: 500; font-size: 13px;">Meta Ads</span>
                                                </div>
                                            <?php else: ?>
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#F59E0B">
                                                        <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                                                    </svg>
                                                    <span style="font-weight: 500; font-size: 13px;">Google Ads</span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= $event['name'] ?></strong></td>
                                        <td style="color: #6B7280; font-size: 13px;">
                                            <?php
                                            $descriptions = [
                                                'Comprador' => 'A conclusão de uma compra, geralmente indicada pelo recebimento de u...',
                                                'Lead Qualificado' => 'Passou para consultor',
                                                'ViewContent' => 'Uma visita a uma página da web importante para você. Por exemplo, um...',
                                                'Lead' => 'O envio de informações por parte do cliente, sabendo que poderá ser ...',
                                                'Purchase' => 'A conclusão de uma compra, geralmente indicada pelo recebimento de u...',
                                                'AddPaymentInfo' => 'A adição de informações de pagamento do cliente durante o processo d...',
                                                'AddToCart' => 'A adição de um item ao carrinho ou cesto de compras. Por exemplo, cl...',
                                                'AddToWishlist' => 'A adição de itens à lista de desejos. Por exemplo, clicar em um botã...',
                                                'CompleteRegistration' => 'O envio de informações por parte de um cliente em troca de fornecime...',
                                                'Contact' => 'Um call to action ou envio via telefone, SMS, email, chat ou outro m...'
                                            ];
                                            echo $descriptions[$event['name']] ?? '-';
                                            ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i:s') ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-secondary">⋯</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/frontend/js/app.js"></script>
</body>
</html>