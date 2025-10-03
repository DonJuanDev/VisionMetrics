<div class="dashboard-grid">
    <!-- Meta Ads -->
    <div class="card">
        <div class="card-header">
            <h2>Meta Ads (Facebook/Instagram)</h2>
        </div>
        <div class="card-body">
            <?php
            $metaConfig = null;
            foreach (($integrations ?? []) as $int) {
                if ($int['type'] === 'meta_ads') {
                    $metaConfig = $int;
                    break;
                }
            }
            ?>
            
            <?php if ($metaConfig && $metaConfig['is_active']): ?>
                <div class="badge badge-success" style="margin-bottom: 16px;">✓ Conectado</div>
                <p class="text-muted">CAPI enviando conversões automaticamente</p>
                <a href="/integrations-config.php#meta" class="btn btn-sm btn-secondary">Configurar</a>
            <?php else: ?>
                <p class="text-muted">Configure para enviar conversões server-side</p>
                <a href="/integrations-config.php#meta" class="btn btn-sm btn-primary">Conectar</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Google Analytics -->
    <div class="card">
        <div class="card-header">
            <h2>Google Analytics 4</h2>
        </div>
        <div class="card-body">
            <?php
            $ga4Config = null;
            foreach (($integrations ?? []) as $int) {
                if ($int['type'] === 'google_analytics') {
                    $ga4Config = $int;
                    break;
                }
            }
            ?>
            
            <?php if ($ga4Config && $ga4Config['is_active']): ?>
                <div class="badge badge-success" style="margin-bottom: 16px;">✓ Conectado</div>
                <p class="text-muted">Eventos sendo enviados via Measurement Protocol</p>
                <a href="/integrations-config.php#ga4" class="btn btn-sm btn-secondary">Configurar</a>
            <?php else: ?>
                <p class="text-muted">Configure para tracking server-side</p>
                <a href="/integrations-config.php#ga4" class="btn btn-sm btn-primary">Conectar</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h2>Todas as Integrações</h2>
    </div>
    <div class="card-body">
        <a href="/integrations-config.php" class="btn btn-primary">Gerenciar Integrações Completas</a>
    </div>
</div>





