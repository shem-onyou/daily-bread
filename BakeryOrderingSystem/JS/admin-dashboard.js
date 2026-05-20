// ── Sidebar toggle ──
const sidebar   = document.getElementById('sidebar');
const overlay   = document.getElementById('overlay');
const toggleBtn = document.getElementById('toggleBtn');

toggleBtn.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
overlay.addEventListener('click',   () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });

// ── Chart colours matching the site palette ──
const BROWN      = '#96715e';
const BROWN_DARK = '#553423';
const BROWN_FILL = 'rgba(150, 113, 94, 0.15)';

// ── Revenue Overview — bar chart ──
async function loadRevenueChart() {
    try {
        const res  = await fetch('/BakeryOrderingSystem/PHP/chart_data.php?type=revenue');
        const json = await res.json();

        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels:   json.labels,
                datasets: [{
                    label:           'Revenue (₱)',
                    data:            json.data,
                    backgroundColor: BROWN_FILL,
                    borderColor:     BROWN,
                    borderWidth:     2,
                    borderRadius:    6,
                    borderSkipped:   false,
                }]
            },
            options: {
                responsive:          true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ₱${ctx.parsed.y.toFixed(2)}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#999', font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f0e8e3' },
                        ticks: {
                            color: '#999',
                            font:  { size: 11 },
                            callback: val => '₱' + val.toLocaleString()
                        }
                    }
                }
            }
        });
    } catch {
        document.getElementById('revenueChart').closest('.chart-canvas-wrap').innerHTML =
            '<p style="text-align:center;color:#999;padding:2rem;">Could not load revenue data.</p>';
    }
}

// ── Weekly Traffic — line chart ──
async function loadTrafficChart() {
    try {
        const res  = await fetch('/BakeryOrderingSystem/PHP/chart_data.php?type=traffic');
        const json = await res.json();

        new Chart(document.getElementById('trafficChart'), {
            type: 'line',
            data: {
                labels:   json.labels,
                datasets: [{
                    label:           'Orders',
                    data:            json.data,
                    borderColor:     BROWN_DARK,
                    backgroundColor: BROWN_FILL,
                    borderWidth:     2,
                    pointBackgroundColor: BROWN_DARK,
                    pointRadius:     4,
                    tension:         0.4,
                    fill:            true,
                }]
            },
            options: {
                responsive:          true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.y} order${ctx.parsed.y !== 1 ? 's' : ''}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#999', font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f0e8e3' },
                        ticks: {
                            color:     '#999',
                            font:      { size: 11 },
                            stepSize:  1,
                            precision: 0
                        }
                    }
                }
            }
        });
    } catch {
        document.getElementById('trafficChart').closest('.chart-canvas-wrap').innerHTML =
            '<p style="text-align:center;color:#999;padding:2rem;">Could not load traffic data.</p>';
    }
}

// ── Logout ──
const logoutModal     = document.getElementById('logoutModal');
const logoutBtn       = document.getElementById('logoutBtn');
const logoutCancelBtn = document.getElementById('logoutCancelBtn');

logoutBtn.addEventListener('click',       () => logoutModal.classList.add('active'));
logoutCancelBtn.addEventListener('click', () => logoutModal.classList.remove('active'));
logoutModal.addEventListener('click', (e) => { if (e.target === logoutModal) logoutModal.classList.remove('active'); });

// ── Init ──
loadRevenueChart();
loadTrafficChart();
