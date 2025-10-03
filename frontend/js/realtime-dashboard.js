// Real-Time Dashboard with Server-Sent Events

(function() {
    'use strict';
    
    let eventSource = null;
    let reconnectTimeout = null;
    
    function initRealtimeDashboard() {
        // Check if we're on dashboard page
        if (!window.location.pathname.includes('dashboard')) {
            return;
        }
        
        connectSSE();
        
        // Add connection status indicator
        addConnectionIndicator();
    }
    
    function connectSSE() {
        if (eventSource) {
            eventSource.close();
        }
        
        eventSource = new EventSource('/dashboard-realtime.php');
        
        eventSource.addEventListener('stats', function(e) {
            const data = JSON.parse(e.data);
            updateDashboardStats(data);
            updateConnectionStatus('connected');
        });
        
        eventSource.addEventListener('error', function(e) {
            console.error('[Real-time] Connection error', e);
            updateConnectionStatus('disconnected');
            
            // Reconnect after 5 seconds
            if (reconnectTimeout) {
                clearTimeout(reconnectTimeout);
            }
            reconnectTimeout = setTimeout(() => {
                console.log('[Real-time] Reconnecting...');
                connectSSE();
            }, 5000);
        });
        
        eventSource.addEventListener('open', function(e) {
            console.log('[Real-time] Connected!');
            updateConnectionStatus('connected');
        });
    }
    
    function updateDashboardStats(data) {
        // Update stat cards
        updateStatValue('total-leads', data.totalLeads);
        updateStatValue('leads-today', data.leadsToday);
        updateStatValue('total-sales', data.totalSales);
        updateStatValue('total-revenue', formatCurrency(data.totalRevenue));
        
        // Show new notifications
        if (data.notifications && data.notifications.length > 0) {
            showNotifications(data.notifications);
        }
        
        // Update timestamp
        const now = new Date();
        const timeEl = document.getElementById('last-update-time');
        if (timeEl) {
            timeEl.textContent = 'Atualizado às ' + now.toLocaleTimeString('pt-BR');
        }
    }
    
    function updateStatValue(id, value) {
        const el = document.getElementById(id);
        if (el) {
            // Add animation
            el.style.transform = 'scale(1.1)';
            el.style.color = '#6366F1';
            
            el.textContent = value.toLocaleString('pt-BR');
            
            setTimeout(() => {
                el.style.transform = 'scale(1)';
                el.style.color = '';
            }, 300);
        }
    }
    
    function showNotifications(notifications) {
        const container = document.getElementById('notifications-container');
        if (!container) return;
        
        notifications.forEach(notif => {
            const toast = document.createElement('div');
            toast.className = 'toast toast-info';
            toast.innerHTML = `
                <strong>${escapeHtml(notif.title)}</strong>
                <p>${escapeHtml(notif.message)}</p>
            `;
            toast.style.cssText = `
                position: fixed;
                bottom: 24px;
                right: 24px;
                background: white;
                padding: 16px 20px;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
                border-left: 4px solid #3B82F6;
                min-width: 300px;
                max-width: 400px;
                z-index: 9999;
                animation: slideIn 0.3s ease-out;
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        });
    }
    
    function addConnectionIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'realtime-indicator';
        indicator.style.cssText = `
            position: fixed;
            top: 80px;
            right: 24px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        `;
        
        indicator.innerHTML = `
            <span id="status-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #10B981;"></span>
            <span id="status-text">Ao vivo</span>
        `;
        
        document.body.appendChild(indicator);
    }
    
    function updateConnectionStatus(status) {
        const indicator = document.getElementById('realtime-indicator');
        const dot = document.getElementById('status-dot');
        const text = document.getElementById('status-text');
        
        if (!indicator) return;
        
        if (status === 'connected') {
            indicator.style.background = '#D1FAE5';
            indicator.style.color = '#065F46';
            dot.style.background = '#10B981';
            text.textContent = 'Ao vivo';
        } else {
            indicator.style.background = '#FEE2E2';
            indicator.style.color = '#991B1B';
            dot.style.background = '#EF4444';
            text.textContent = 'Desconectado';
        }
    }
    
    function formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRealtimeDashboard);
    } else {
        initRealtimeDashboard();
    }
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (eventSource) {
            eventSource.close();
        }
    });
    
})();





