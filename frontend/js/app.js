// VisionMetrics App JS - Clean & Professional

document.addEventListener('DOMContentLoaded', function() {
    initAlerts();
    initConfirmButtons();
    initCopyButtons();
});

// Auto-hide alerts
function initAlerts() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            opacity: 0.6;
            margin-left: auto;
            padding: 0 4px;
            color: inherit;
        `;
        closeBtn.addEventListener('click', () => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        });
        closeBtn.addEventListener('mouseenter', () => closeBtn.style.opacity = '1');
        closeBtn.addEventListener('mouseleave', () => closeBtn.style.opacity = '0.6');
        
        alert.appendChild(closeBtn);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

// Confirm delete actions
function initConfirmButtons() {
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Tem certeza?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

// Copy to clipboard
function initCopyButtons() {
    const copyButtons = document.querySelectorAll('[data-copy]');
    copyButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const text = this.dataset.copy || this.textContent;
            copyToClipboard(text);
        });
    });
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Copiado com sucesso!', 'success');
        }).catch(() => {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.cssText = 'position: fixed; left: -9999px;';
    document.body.appendChild(textarea);
    textarea.select();
    
    try {
        document.execCommand('copy');
        showToast('Copiado com sucesso!', 'success');
    } catch (err) {
        showToast('Erro ao copiar', 'error');
    }
    
    document.body.removeChild(textarea);
}

// Toast notifications
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icons = {
        success: '✓',
        error: '✕',
        info: 'ℹ',
        warning: '⚠'
    };
    
    const colors = {
        success: '#10B981',
        error: '#EF4444',
        info: '#3B82F6',
        warning: '#F59E0B'
    };
    
    toast.innerHTML = `
        <div style="
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: ${colors[type]};
            color: white;
            font-weight: bold;
            font-size: 14px;
            flex-shrink: 0;
        ">${icons[type] || icons.info}</div>
        <span style="flex: 1; font-size: 14px; font-weight: 500;">${message}</span>
    `;
    
    toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 12px;
        background: white;
        padding: 16px 20px;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        min-width: 300px;
        max-width: 400px;
        border: 1px solid #E5E7EB;
        border-left: 4px solid ${colors[type]};
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Utility functions
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

function formatPhone(phone) {
    phone = phone.replace(/\D/g, '');
    if (phone.length === 13) {
        return `+${phone.substr(0, 2)} (${phone.substr(2, 2)}) ${phone.substr(4, 5)}-${phone.substr(9)}`;
    } else if (phone.length === 11) {
        return `(${phone.substr(0, 2)}) ${phone.substr(2, 5)}-${phone.substr(7)}`;
    }
    return phone;
}

function timeAgo(timestamp) {
    const now = new Date();
    const then = new Date(timestamp);
    const diff = Math.floor((now - then) / 1000);
    
    if (diff < 60) return 'agora há pouco';
    if (diff < 3600) return `${Math.floor(diff / 60)} min atrás`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h atrás`;
    if (diff < 604800) return `${Math.floor(diff / 86400)} dias atrás`;
    
    return then.toLocaleDateString('pt-BR');
}

// Export to global
window.VisionMetrics = {
    showToast,
    copyToClipboard,
    formatCurrency,
    formatPhone,
    timeAgo
};
