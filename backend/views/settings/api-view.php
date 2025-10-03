<?php
$stmt = $db->prepare("SELECT * FROM api_keys WHERE workspace_id = ? ORDER BY created_at DESC");
$stmt->execute([$currentWorkspace['id']]);
$apiKeys = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h2>API Keys</h2>
    </div>
    <div class="card-body">
        <?php if (empty($apiKeys)): ?>
            <p>Nenhuma API key criada. Crie uma em Configurações > API Keys</p>
            <a href="/settings.php?tab=api" class="btn btn-primary">Gerenciar API Keys</a>
        <?php else: ?>
            <?php foreach ($apiKeys as $key): ?>
                <div style="background: #F9FAFB; padding: 16px; border-radius: 8px; margin-bottom: 12px;">
                    <code style="font-size: 13px;"><?= htmlspecialchars($key['api_key']) ?></code>
                    <button onclick="copyToClipboard('<?= htmlspecialchars($key['api_key']) ?>')" class="btn btn-sm btn-secondary" style="margin-left: 12px;">Copiar</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top: 20px; background: #EFF6FF; border-left: 4px solid #3B82F6;">
    <div class="card-body">
        <h3>Documentação da API</h3>
        <p>Endpoint de tracking:</p>
        <code>POST <?= APP_URL ?>/track.php</code>
        <p style="margin-top: 16px;">Exemplo de uso:</p>
        <pre style="background: #1F2937; color: #E5E7EB; padding: 16px; border-radius: 8px; font-size: 12px; overflow-x: auto;">
fetch('<?= APP_URL ?>/track.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    api_key: 'SUA_API_KEY',
    event_type: 'page_view',
    email: 'user@example.com',
    page_url: window.location.href
  })
});
        </pre>
    </div>
</div>


