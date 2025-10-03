// Charts for VisionMetrics - Clean & Professional

document.addEventListener('DOMContentLoaded', function() {
    // Attribution Chart (Pie)
    const attributionCanvas = document.getElementById('attributionChart');
    if (attributionCanvas && typeof attributionData !== 'undefined') {
        drawPieChart(attributionCanvas, attributionData, 'source');
    }

    // Timeline Chart (Line)
    const timelineCanvas = document.getElementById('timelineChart');
    if (timelineCanvas && typeof dailyConversations !== 'undefined') {
        drawLineChart(timelineCanvas, dailyConversations);
    }

    // Stages Chart (Doughnut)
    const stagesCanvas = document.getElementById('stagesChart');
    if (stagesCanvas && typeof leadStages !== 'undefined') {
        drawPieChart(stagesCanvas, leadStages, 'stage');
    }

    // Responsive
    window.addEventListener('resize', debounce(() => {
        if (attributionCanvas) drawPieChart(attributionCanvas, attributionData, 'source');
        if (timelineCanvas) drawLineChart(timelineCanvas, dailyConversations);
        if (stagesCanvas) drawPieChart(stagesCanvas, leadStages, 'stage');
    }, 250));
});

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

// Pie/Doughnut Chart
function drawPieChart(canvas, data, labelKey) {
    const ctx = canvas.getContext('2d');
    const width = canvas.width = canvas.offsetWidth;
    const height = canvas.height = 300;
    
    const centerX = width / 2;
    const centerY = height / 2;
    const radius = Math.min(centerX, centerY) - 70;
    
    const total = data.reduce((sum, item) => sum + parseInt(item.count), 0);
    
    if (total === 0) {
        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = '#9CA3AF';
        ctx.font = '14px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Nenhum dado disponível', centerX, centerY);
        return;
    }
    
    const colors = ['#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#EC4899', '#14B8A6'];
    
    let currentAngle = -Math.PI / 2;
    
    ctx.clearRect(0, 0, width, height);
    
    // Draw slices
    data.forEach((item, index) => {
        const sliceAngle = (parseInt(item.count) / total) * 2 * Math.PI;
        
        ctx.fillStyle = colors[index % colors.length];
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sliceAngle);
        ctx.closePath();
        ctx.fill();
        
        // Border
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 2;
        ctx.stroke();
        
        currentAngle += sliceAngle;
    });
    
    // Draw labels
    currentAngle = -Math.PI / 2;
    data.forEach((item, index) => {
        const sliceAngle = (parseInt(item.count) / total) * 2 * Math.PI;
        const midAngle = currentAngle + sliceAngle / 2;
        
        const labelX = centerX + Math.cos(midAngle) * (radius + 40);
        const labelY = centerY + Math.sin(midAngle) * (radius + 40);
        
        const label = item[labelKey] || 'N/A';
        const percentage = ((item.count / total) * 100).toFixed(1);
        
        ctx.fillStyle = '#111827';
        ctx.font = '600 12px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.textAlign = labelX > centerX ? 'left' : 'right';
        ctx.fillText(`${label} (${percentage}%)`, labelX, labelY);
        
        currentAngle += sliceAngle;
    });
}

// Line Chart
function drawLineChart(canvas, data) {
    const ctx = canvas.getContext('2d');
    const width = canvas.width = canvas.offsetWidth;
    const height = canvas.height = 300;
    
    const padding = 50;
    const chartWidth = width - padding * 2;
    const chartHeight = height - padding * 2;
    
    if (data.length === 0) {
        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = '#9CA3AF';
        ctx.font = '14px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Nenhum dado disponível', width / 2, height / 2);
        return;
    }
    
    ctx.clearRect(0, 0, width, height);
    
    const maxCount = Math.max(...data.map(d => parseInt(d.count)), 1);
    const maxSales = Math.max(...data.map(d => parseInt(d.sales || 0)), 1);
    const maxValue = Math.max(maxCount, maxSales);
    const step = chartWidth / (data.length - 1 || 1);
    
    // Draw grid
    ctx.strokeStyle = '#F3F4F6';
    ctx.lineWidth = 1;
    
    for (let i = 0; i <= 5; i++) {
        const y = padding + (chartHeight / 5) * i;
        ctx.beginPath();
        ctx.moveTo(padding, y);
        ctx.lineTo(width - padding, y);
        ctx.stroke();
        
        // Y-axis labels
        const value = Math.round(maxValue * (1 - i / 5));
        ctx.fillStyle = '#6B7280';
        ctx.font = '11px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.textAlign = 'right';
        ctx.fillText(value.toString(), padding - 10, y + 4);
    }
    
    // Draw axes
    ctx.strokeStyle = '#E5E7EB';
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(padding, padding);
    ctx.lineTo(padding, height - padding);
    ctx.lineTo(width - padding, height - padding);
    ctx.stroke();
    
    // Draw conversations line
    ctx.strokeStyle = '#4F46E5';
    ctx.lineWidth = 3;
    ctx.beginPath();
    
    data.forEach((item, index) => {
        const x = padding + index * step;
        const y = height - padding - (parseInt(item.count) / maxValue) * chartHeight;
        
        if (index === 0) {
            ctx.moveTo(x, y);
        } else {
            ctx.lineTo(x, y);
        }
    });
    
    ctx.stroke();
    
    // Draw sales line
    if (data.some(d => d.sales)) {
        ctx.strokeStyle = '#10B981';
        ctx.lineWidth = 3;
        ctx.beginPath();
        
        data.forEach((item, index) => {
            const x = padding + index * step;
            const y = height - padding - (parseInt(item.sales || 0) / maxValue) * chartHeight;
            
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        
        ctx.stroke();
    }
    
    // Draw points and labels
    data.forEach((item, index) => {
        const x = padding + index * step;
        const yConv = height - padding - (parseInt(item.count) / maxValue) * chartHeight;
        
        // Conversations point
        ctx.fillStyle = '#4F46E5';
        ctx.beginPath();
        ctx.arc(x, yConv, 4, 0, 2 * Math.PI);
        ctx.fill();
        
        // Sales point
        if (item.sales) {
            const ySales = height - padding - (parseInt(item.sales) / maxValue) * chartHeight;
            ctx.fillStyle = '#10B981';
            ctx.beginPath();
            ctx.arc(x, ySales, 4, 0, 2 * Math.PI);
            ctx.fill();
        }
        
        // X-axis label
        ctx.fillStyle = '#6B7280';
        ctx.font = '11px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.textAlign = 'center';
        const date = new Date(item.date);
        const label = (date.getMonth() + 1) + '/' + date.getDate();
        ctx.fillText(label, x, height - padding + 20);
    });
    
    // Legend
    ctx.fillStyle = '#4F46E5';
    ctx.fillRect(width - 150, 20, 16, 3);
    ctx.fillStyle = '#111827';
    ctx.font = '600 12px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    ctx.textAlign = 'left';
    ctx.fillText('Conversas', width - 130, 23);
    
    if (data.some(d => d.sales)) {
        ctx.fillStyle = '#10B981';
        ctx.fillRect(width - 150, 35, 16, 3);
        ctx.fillStyle = '#111827';
        ctx.fillText('Vendas', width - 130, 38);
    }
}
