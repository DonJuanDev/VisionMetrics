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
                if ($int['provider'] === 'meta') {
                    $metaConfig = $int;
                    break;
                }
            }
            ?>
            
            <?php if ($metaConfig && $metaConfig['is_active']): ?>
                <div class="badge badge-success" style="margin-bottom: 16px;">✓ Conectado</div>
                <p class="text-muted">CAPI enviando conversões automaticamente</p>
                <div style="margin-top: 12px;">
                    <button onclick="testIntegration('meta')" class="btn btn-sm btn-outline-primary">Testar</button>
                    <a href="/integrations-config.php#meta" class="btn btn-sm btn-secondary">Configurar</a>
                </div>
            <?php else: ?>
                <p class="text-muted">Configure para enviar conversões server-side</p>
                <div style="margin-top: 12px;">
                    <button onclick="testIntegration('meta')" class="btn btn-sm btn-outline-primary">Testar</button>
                    <a href="/integrations-config.php#meta" class="btn btn-sm btn-primary">Conectar</a>
                </div>
            <?php endif; ?>
            
            <div id="meta-test-result" style="margin-top: 12px;"></div>
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
                if ($int['provider'] === 'ga4') {
                    $ga4Config = $int;
                    break;
                }
            }
            ?>
            
            <?php if ($ga4Config && $ga4Config['is_active']): ?>
                <div class="badge badge-success" style="margin-bottom: 16px;">✓ Conectado</div>
                <p class="text-muted">Eventos sendo enviados via Measurement Protocol</p>
                <div style="margin-top: 12px;">
                    <button onclick="testIntegration('ga4')" class="btn btn-sm btn-outline-primary">Testar</button>
                    <a href="/integrations-config.php#ga4" class="btn btn-sm btn-secondary">Configurar</a>
                </div>
            <?php else: ?>
                <p class="text-muted">Configure para tracking server-side</p>
                <div style="margin-top: 12px;">
                    <button onclick="testIntegration('ga4')" class="btn btn-sm btn-outline-primary">Testar</button>
                    <a href="/integrations-config.php#ga4" class="btn btn-sm btn-primary">Conectar</a>
                </div>
            <?php endif; ?>
            
            <div id="ga4-test-result" style="margin-top: 12px;"></div>
        </div>
    </div>

    <!-- TikTok Ads -->
    <div class="card">
        <div class="card-header">
            <h2>TikTok Ads</h2>
        </div>
        <div class="card-body">
            <?php
            $tiktokConfig = null;
            foreach (($integrations ?? []) as $int) {
                if ($int['provider'] === 'tiktok') {
                    $tiktokConfig = $int;
                    break;
                }
            }
            ?>
            
            <?php if ($tiktokConfig && $tiktokConfig['is_active']): ?>
                <div class="badge badge-success" style="margin-bottom: 16px;">✓ Conectado</div>
                <p class="text-muted">Eventos sendo enviados via Events API</p>
                <div style="margin-top: 12px;">
                    <button onclick="testIntegration('tiktok')" class="btn btn-sm btn-outline-primary">Testar</button>
                    <a href="/integrations-config.php#tiktok" class="btn btn-sm btn-secondary">Configurar</a>
                </div>
            <?php else: ?>
                <p class="text-muted">Configure para tracking server-side</p>
                <div style="margin-top: 12px;">
                    <button onclick="testIntegration('tiktok')" class="btn btn-sm btn-outline-primary">Testar</button>
                    <a href="/integrations-config.php#tiktok" class="btn btn-sm btn-primary">Conectar</a>
                </div>
            <?php endif; ?>
            
            <div id="tiktok-test-result" style="margin-top: 12px;"></div>
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

<script>
async function testIntegration(provider) {
    const resultDiv = document.getElementById(provider + '-test-result');
    resultDiv.innerHTML = '<div class="alert alert-info">Testando integração...</div>';
    
    try {
        const response = await fetch('/backend/api/test-integrations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=test&provider=${provider}&csrf_token=${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <strong>✓ Teste bem-sucedido!</strong><br>
                    ${result.mode ? `Modo: ${result.mode}` : ''}
                    ${result.message ? `<br>${result.message}` : ''}
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-error">
                    <strong>✗ Teste falhou</strong><br>
                    ${result.error || 'Erro desconhecido'}
                </div>
            `;
        }
    } catch (error) {
        resultDiv.innerHTML = `
            <div class="alert alert-error">
                <strong>✗ Erro de conexão</strong><br>
                ${error.message}
            </div>
        `;
    }
}
</script>





