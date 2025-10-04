<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="/backend/dashboard.php" class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M13 2L3 14h8l-1 8 10-12h-8l1-8z" fill="white"/>
                </svg>
            </div>
            <span class="sidebar-logo-text"><?= APP_NAME ?></span>
        </a>
        <button class="sidebar-collapse-btn" onclick="toggleSidebar()">
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    <div class="sidebar-menu">
        <!-- DASHBOARD -->
        <div class="sidebar-folder">
            <div class="sidebar-folder-header" onclick="toggleFolder('dashboard')">
                <div class="sidebar-folder-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard & Métricas
                </div>
                <svg class="folder-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
            <div class="sidebar-folder-content" id="dashboard">
                <a href="/backend/dashboard.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>

                <a href="/backend/metrics.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'metrics.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Métricas Avançadas
                </a>

                <a href="/backend/reports.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Relatórios
                </a>
            </div>
        </div>

        <!-- LEADS & CONVERSAS -->
        <div class="sidebar-folder">
            <div class="sidebar-folder-header" onclick="toggleFolder('leads')">
                <div class="sidebar-folder-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    👥 Leads & Conversas
                </div>
                <svg class="folder-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
            <div class="sidebar-folder-content" id="leads">
                <a href="/backend/leads.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'leads.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Leads
                </a>

                <a href="/backend/conversations.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'conversations.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Conversas
                </a>

                <a href="/backend/journey.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'journey.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Jornada do Cliente
                </a>
            </div>
        </div>

        <!-- TRACKING & EVENTOS -->
        <div class="sidebar-folder">
            <div class="sidebar-folder-header" onclick="toggleFolder('tracking')">
                <div class="sidebar-folder-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    🎯 Tracking & Eventos
                </div>
                <svg class="folder-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
            <div class="sidebar-folder-content" id="tracking">
                <a href="/backend/events.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'events.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                    </svg>
                    Eventos de Conversão
                </a>

                <a href="/backend/pixel-tracking.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'pixel-tracking.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Disparos de Pixel
                </a>

                <a href="/backend/events-replay.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'events-replay.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Replay de Eventos
                </a>
            </div>
        </div>

        <!-- CAMPANHAS & LINKS -->
        <div class="sidebar-folder">
            <div class="sidebar-folder-header" onclick="toggleFolder('campaigns')">
                <div class="sidebar-folder-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    🔗 Campanhas & Links
                </div>
                <svg class="folder-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
            <div class="sidebar-folder-content" id="campaigns">
                <a href="/backend/trackable-links.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'trackable-links.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    Links Rastreáveis
                </a>

                <a href="/backend/campaigns.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'campaigns.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    Mensagens Rastreáveis
                </a>

                <a href="/backend/sales.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'sales.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                    Vendas
                </a>
            </div>
        </div>

        <!-- INTEGRAÇÕES & APIs -->
        <div class="sidebar-folder">
            <div class="sidebar-folder-header" onclick="toggleFolder('integrations')">
                <div class="sidebar-folder-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    🔌 Integrações & APIs
                </div>
                <svg class="folder-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
            <div class="sidebar-folder-content" id="integrations">
                <a href="/backend/integrations/integrations-config.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'integrations-config.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Integrações
                </a>

                <a href="/backend/integrations/webhooks.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'webhooks.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Webhooks
                </a>
            </div>
        </div>

        <!-- CONFIGURAÇÕES & ADMIN -->
        <div class="sidebar-folder">
            <div class="sidebar-folder-header" onclick="toggleFolder('admin')">
                <div class="sidebar-folder-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    ⚙️ Configurações & Admin
                </div>
                <svg class="folder-arrow" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
            <div class="sidebar-folder-content" id="admin">
                <a href="/backend/admin/settings.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Configurações
                </a>

                <a href="/backend/admin/billing.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'billing.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Billing
                </a>

                <a href="/backend/help.php" class="sidebar-menu-item <?= basename($_SERVER['PHP_SELF']) === 'help.php' ? 'active' : '' ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Central de Ajuda
                </a>
            </div>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= htmlspecialchars(substr($currentUser['name'], 0, 15)) ?></div>
                <div class="sidebar-user-role"><?= htmlspecialchars(substr($currentWorkspace['name'], 0, 18)) ?></div>
            </div>
            <a href="/backend/logout.php" style="color: var(--text-muted); font-size: 16px; padding: 6px; border-radius: var(--radius); transition: all var(--transition-normal);" title="Sair" onmouseover="this.style.background='var(--bg-glass-hover)'; this.style.color='var(--text-primary)'" onmouseout="this.style.background='transparent'; this.style.color='var(--text-muted)'">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </a>
        </div>
    </div>
</div>

<script>
function toggleFolder(folderId) {
    const folder = document.getElementById(folderId);
    const arrow = folder.previousElementSibling.querySelector('.folder-arrow');
    const folderElement = folder.previousElementSibling.parentElement;
    
    if (folder.classList.contains('open')) {
        // Fechar pasta
        folder.classList.remove('open');
        arrow.style.transform = 'rotate(0deg)';
        folderElement.style.background = 'rgba(255, 255, 255, 0.02)';
    } else {
        // Fechar todas as outras pastas primeiro
        document.querySelectorAll('.sidebar-folder-content.open').forEach(openFolder => {
            if (openFolder.id !== folderId) {
                openFolder.classList.remove('open');
                const openArrow = openFolder.previousElementSibling.querySelector('.folder-arrow');
                openArrow.style.transform = 'rotate(0deg)';
                openFolder.previousElementSibling.parentElement.style.background = 'rgba(255, 255, 255, 0.02)';
            }
        });
        
        // Abrir pasta atual
        folder.classList.add('open');
        arrow.style.transform = 'rotate(90deg)';
        folderElement.style.background = 'rgba(79, 70, 229, 0.1)';
    }
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    
    sidebar.classList.toggle('collapsed');
    
    // Salvar estado no localStorage
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

// Inicializar pastas fechadas por padrão
document.addEventListener('DOMContentLoaded', function() {
    // Restaurar estado do sidebar
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        document.getElementById('sidebar').classList.add('collapsed');
    }
    
    // Fechar todas as pastas por padrão
    document.querySelectorAll('.sidebar-folder-content').forEach(folder => {
        folder.classList.remove('open');
        const arrow = folder.previousElementSibling.querySelector('.folder-arrow');
        arrow.style.transform = 'rotate(0deg)';
    });
    
    // Apenas abrir a pasta da página atual se necessário
    const currentPage = '<?= basename($_SERVER['PHP_SELF']) ?>';
    const folderMap = {
        'dashboard.php': 'dashboard',
        'metrics.php': 'dashboard',
        'reports.php': 'dashboard',
        'leads.php': 'leads',
        'conversations.php': 'leads',
        'journey.php': 'leads',
        'events.php': 'tracking',
        'pixel-tracking.php': 'tracking',
        'events-replay.php': 'tracking',
        'trackable-links.php': 'campaigns',
        'campaigns.php': 'campaigns',
        'sales.php': 'campaigns',
        'integrations-config.php': 'integrations',
        'webhooks.php': 'integrations',
        'settings.php': 'admin',
        'billing.php': 'admin',
        'help.php': 'admin'
    };
    
    const folderId = folderMap[currentPage];
    if (folderId) {
        // Pequeno delay para mostrar a animação
        setTimeout(() => {
            const folder = document.getElementById(folderId);
            const arrow = folder.previousElementSibling.querySelector('.folder-arrow');
            const folderElement = folder.previousElementSibling.parentElement;
            
            folder.classList.add('open');
            arrow.style.transform = 'rotate(90deg)';
            folderElement.style.background = 'rgba(79, 70, 229, 0.1)';
        }, 100);
    }
});
</script>