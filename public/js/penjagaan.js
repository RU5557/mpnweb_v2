// resources/js/penjagaan.js - DJP Executive Chart Helper
// Pakai Chart.js CDN yang udah ada di layouts/app.blade.php

function mpnwebGrad(ctx, type) {
    const h = ctx.canvas.height || 400;
    if (type === 'navy-solid') {
        const g = ctx.createLinearGradient(0, 0, 0, h);
        g.addColorStop(0, 'rgba(15,23,42,0.95)'); g.addColorStop(1, 'rgba(30,58,138,0.85)');
        return g;
    }
    if (type === 'gold-solid') {
        const g = ctx.createLinearGradient(0, 0, 0, h);
        g.addColorStop(0, 'rgba(245,158,11,0.9)'); g.addColorStop(1, 'rgba(252,211,77,0.6)');
        return g;
    }
    if (type === 'navy-trans') {
        const g = ctx.createLinearGradient(0, 0, 0, h);
        g.addColorStop(0, 'rgba(15,23,42,0.25)'); g.addColorStop(1, 'rgba(15,23,42,0.02)');
        return g;
    }
    if (type === 'gold-trans') {
        const g = ctx.createLinearGradient(0, 0, 0, h);
        g.addColorStop(0, 'rgba(245,158,11,0.28)'); g.addColorStop(1, 'rgba(245,158,11,0.02)');
        return g;
    }
    if (type === 'navy-trans-vs') {
        const g = ctx.createLinearGradient(0, 0, 0, h);
        g.addColorStop(0, 'rgba(15,23,42,0.22)'); g.addColorStop(1, 'rgba(15,23,42,0.01)');
        return g;
    }
    if (type === 'gold-trans-vs') {
        const g = ctx.createLinearGradient(0, 0, 0, h);
        g.addColorStop(0, 'rgba(245,158,11,0.24)'); g.addColorStop(1, 'rgba(245,158,11,0.01)');
        return g;
    }
    return 'rgba(15,23,42,0.9)';
}

function mpnwebTooltipRp() {
    return {
        padding: 12, cornerRadius: 8, backgroundColor: '#0f172a',
        titleFont: { family: 'Inter', size: 12, weight: 'bold' },
        bodyFont: { family: 'JetBrains Mono', size: 11 },
        callbacks: {
            label: (c) => c.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(c.raw || 0)
        }
    }
}

window.renderChartBulanan = function (canvasId, labels, dataIni, dataLalu, labelIni, labelLalu) {
    const el = document.getElementById(canvasId); if (!el) return;
    const old = Chart.getChart(el); if (old) old.destroy();
    const ctx = el.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: labelIni, data: dataIni, backgroundColor: mpnwebGrad(ctx, 'navy-solid'), borderRadius: 4, categoryPercentage: 0.6, barPercentage: 0.7 },
                { label: labelLalu, data: dataLalu, backgroundColor: mpnwebGrad(ctx, 'gold-solid'), borderRadius: 4, categoryPercentage: 0.6, barPercentage: 0.7 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8, font: { family: 'Inter', size: 11, weight: '700' }, color: '#0f172a' } },
                tooltip: mpnwebTooltipRp()
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 11, weight: '600' }, color: '#64748b' } },
                y: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.8)' }, ticks: { font: { family: 'JetBrains Mono', size: 10, weight: '500' }, color: '#64748b', callback: (v) => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(v) } }
            }
        }
    });
}

window.renderChartHarian = function (canvasId, labels, dataIni, dataLalu, labelIni, labelLalu) {
    const el = document.getElementById(canvasId); if (!el) return;
    const old = Chart.getChart(el); if (old) old.destroy();
    const ctx = el.getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                { label: labelIni, data: dataIni, borderColor: 'rgba(15,23,42,0.9)', backgroundColor: mpnwebGrad(ctx, 'navy-trans'), borderWidth: 2.5, fill: true, tension: 0.35, pointRadius: 0, pointHoverRadius: 5 },
                { label: labelLalu, data: dataLalu, borderColor: 'rgba(245,158,11,0.85)', backgroundColor: mpnwebGrad(ctx, 'gold-trans'), borderWidth: 2, borderDash: [6, 4], fill: true, tension: 0.35, pointRadius: 0, pointHoverRadius: 5 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8, font: { family: 'Inter', size: 11, weight: '700' }, color: '#0f172a' } },
                tooltip: mpnwebTooltipRp()
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 11, weight: '600' }, color: '#64748b' } },
                y: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' }, ticks: { font: { family: 'JetBrains Mono', size: 10, weight: '500' }, color: '#64748b', callback: (v) => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(v) } }
            }
        }
    });
}

window.renderChartVs = function (canvasId, labels, dataIni, dataLalu, labelIni, labelLalu) {
    const el = document.getElementById(canvasId); if (!el) return;
    const old = Chart.getChart(el); if (old) old.destroy();
    const ctx = el.getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                { label: labelIni, data: dataIni, borderColor: 'rgba(15,23,42,0.9)', backgroundColor: mpnwebGrad(ctx, 'navy-trans-vs'), borderWidth: 2.8, fill: true, tension: 0.4, pointRadius: 0, pointHoverRadius: 6 },
                { label: labelLalu, data: dataLalu, borderColor: 'rgba(245,158,11,0.85)', backgroundColor: mpnwebGrad(ctx, 'gold-trans-vs'), borderWidth: 2.2, borderDash: [8, 5], fill: true, tension: 0.4, pointRadius: 0, pointHoverRadius: 6 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8, boxHeight: 8, font: { family: 'Inter', size: 11, weight: '700' }, color: '#0f172a' } },
                tooltip: {
                    padding: 12, cornerRadius: 8, backgroundColor: '#0f172a',
                    titleFont: { family: 'Inter', size: 12, weight: 'bold' }, bodyFont: { family: 'JetBrains Mono', size: 11 },
                    callbacks: {
                        label: (c) => c.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(c.raw || 0),
                        afterBody: (items) => {
                            if (items.length === 2) {
                                const diff = items[0].raw - items[1].raw;
                                const pct = items[1].raw != 0 ? (diff / items[1].raw * 100) : 0;
                                return 'Selisih: Rp ' + new Intl.NumberFormat('id-ID').format(diff) + ' (' + pct.toFixed(1) + '%)';
                            }
                            return '';
                        }
                    }
                }
            },
            scales: {
                x: { title: { display: true, text: 'Tanggal', font: { size: 11, weight: '600' }, color: '#64748b' }, grid: { display: false }, ticks: { font: { family: 'Inter', size: 11, weight: '600' }, color: '#64748b' } },
                y: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' }, ticks: { font: { family: 'JetBrains Mono', size: 10, weight: '500' }, color: '#64748b', callback: (v) => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(v) } }
            }
        }
    });
}
