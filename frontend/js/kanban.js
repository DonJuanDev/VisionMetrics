// Kanban Drag and Drop - Clean & Professional

document.addEventListener('DOMContentLoaded', function() {
    initKanban();
});

function initKanban() {
    const cards = document.querySelectorAll('.kanban-card');
    const columns = document.querySelectorAll('.kanban-column-body');

    // Make cards draggable
    cards.forEach(card => {
        card.setAttribute('draggable', 'true');
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });

    // Make columns droppable
    columns.forEach(column => {
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('drop', handleDrop);
        column.addEventListener('dragleave', handleDragLeave);
        column.addEventListener('dragenter', handleDragEnter);
    });

    let draggedCard = null;
    let sourceColumn = null;

    function handleDragStart(e) {
        draggedCard = this;
        sourceColumn = this.closest('.kanban-column-body');
        
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
        
        // Add visual feedback to columns
        columns.forEach(col => {
            if (col !== sourceColumn) {
                col.classList.add('can-drop');
            }
        });
    }

    function handleDragEnd(e) {
        this.classList.remove('dragging');
        
        columns.forEach(col => {
            col.classList.remove('column-drag-over', 'can-drop');
        });
    }

    function handleDragEnter(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        
        if (this !== sourceColumn && draggedCard) {
            this.classList.add('column-drag-over');
        }
        
        return false;
    }

    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    function handleDragLeave(e) {
        if (e.target === this) {
            this.classList.remove('column-drag-over');
        }
    }

    function handleDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }

        this.classList.remove('column-drag-over');

        if (draggedCard) {
            const leadId = draggedCard.getAttribute('data-lead-id');
            const newStage = this.closest('.kanban-column').getAttribute('data-stage');
            const oldStage = sourceColumn.closest('.kanban-column').getAttribute('data-stage');
            
            // Don't do anything if dropped in same column
            if (newStage === oldStage) {
                return false;
            }
            
            // Show loading state
            draggedCard.style.opacity = '0.6';
            draggedCard.style.pointerEvents = 'none';
            
            // Update UI immediately
            this.insertBefore(draggedCard, this.firstChild);
            
            // Send to server
            fetch(window.location.href, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `update_stage=1&lead_id=${leadId}&new_stage=${newStage}`
            })
            .then(r => r.json())
            .then(data => {
                draggedCard.style.opacity = '1';
                draggedCard.style.pointerEvents = 'auto';
                
                if (data.success) {
                    draggedCard.classList.add('drop-success');
                    setTimeout(() => draggedCard.classList.remove('drop-success'), 400);
                    
                    showKanbanToast(`Lead movido para ${formatStageName(newStage)}`, 'success');
                    updateColumnCounters();
                    
                    // Confetti for won deals
                    if (newStage === 'ganho') {
                        createConfetti(draggedCard);
                    }
                } else {
                    // Move back on error
                    sourceColumn.appendChild(draggedCard);
                    draggedCard.classList.add('drop-error');
                    setTimeout(() => draggedCard.classList.remove('drop-error'), 400);
                    
                    showKanbanToast('Erro ao mover lead', 'error');
                    updateColumnCounters();
                }
            })
            .catch(err => {
                draggedCard.style.opacity = '1';
                draggedCard.style.pointerEvents = 'auto';
                sourceColumn.appendChild(draggedCard);
                draggedCard.classList.add('drop-error');
                setTimeout(() => draggedCard.classList.remove('drop-error'), 400);
                
                showKanbanToast('Erro de conexão', 'error');
                updateColumnCounters();
            });
        }

        return false;
    }

    function updateColumnCounters() {
        document.querySelectorAll('.kanban-column').forEach(column => {
            const count = column.querySelectorAll('.kanban-card').length;
            const counterEl = column.querySelector('.count');
            
            if (counterEl) {
                counterEl.textContent = count + ' leads';
            }
        });
    }

    function formatStageName(stage) {
        const names = {
            'novo': 'Novo',
            'contatado': 'Contatado',
            'qualificado': 'Qualificado',
            'proposta': 'Proposta',
            'negociacao': 'Negociação',
            'ganho': 'Ganho 🎉',
            'perdido': 'Perdido'
        };
        return names[stage] || stage;
    }
}

// Toast notification
function showKanbanToast(message, type = 'success') {
    const container = getOrCreateToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `kanban-toast kanban-toast-${type}`;
    
    const icons = {
        success: '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M7 10l2 2 4-4" stroke="#10B981" stroke-width="2" stroke-linecap="round"/></svg>',
        error: '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M6 6l8 8M14 6l-8 8" stroke="#EF4444" stroke-width="2" stroke-linecap="round"/></svg>',
        info: '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 6v4M10 14h.01" stroke="#3B82F6" stroke-width="2" stroke-linecap="round"/></svg>'
    };
    
    toast.innerHTML = `
        <div class="toast-icon">${icons[type] || icons.info}</div>
        <div class="toast-message">${message}</div>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function getOrCreateToastContainer() {
    let container = document.querySelector('.kanban-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'kanban-toast-container';
        document.body.appendChild(container);
    }
    return container;
}

// Confetti effect
function createConfetti(element) {
    const rect = element.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;
    
    const colors = ['#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];
    
    for (let i = 0; i < 30; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.left = centerX + 'px';
        confetti.style.top = centerY + 'px';
        
        const angle = (Math.PI * 2 * i) / 30;
        const velocity = 80 + Math.random() * 80;
        const vx = Math.cos(angle) * velocity;
        const vy = Math.sin(angle) * velocity - 80;
        
        confetti.style.setProperty('--vx', vx + 'px');
        confetti.style.setProperty('--vy', vy + 'px');
        
        document.body.appendChild(confetti);
        
        setTimeout(() => confetti.remove(), 1000);
    }
}
