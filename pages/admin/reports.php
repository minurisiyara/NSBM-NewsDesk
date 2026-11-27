<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/components/admin-layout.php';

// Example query counts — keep your existing PDO/MySQL queries from nsbm_newsdesk
$categoryLabels = ['Academic', 'Sports', 'Events', 'Research'];
$categoryCounts = [9, 7, 7, 7];

$monthLabels = ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'];
$monthCounts = [3, 5, 4, 6, 7, 25];

admin_render_start('reports.php', 'Reports');
?>

<div class="stats">
    <div class="card stat">
        <b>30</b>
        <span>Total Articles</span>
    </div>
    <div class="card stat">
        <b>10</b>
        <span>Total Comments</span>
    </div>
    <div class="card stat">
        <b>4</b>
        <span>Registered Users</span>
    </div>
</div>

<div class="two" style="grid-template-columns:1fr 1.4fr;margin-top:0">
    <div class="card pad">
        <h3 style="font-family:Inter,sans-serif;margin-bottom:10px">Articles by Category</h3>
        <div class="chart-box">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <div class="card pad">
        <h3 style="font-family:Inter,sans-serif;margin-bottom:10px">Monthly Overview</h3>
        <div class="chart-box">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    const catCtx = document.getElementById('categoryChart');
    if (catCtx) {
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($categoryLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    data: <?= json_encode($categoryCounts) ?>,
                    backgroundColor: ['#0b4b32', '#2f8f66', '#d08a3c', '#3b82a6', '#7c6bc4', '#dc2626']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    const monthCtx = document.getElementById('monthlyChart');
    if (monthCtx) {
        new Chart(monthCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($monthLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    label: 'Published',
                    data: <?= json_encode($monthCounts) ?>,
                    backgroundColor: '#0b4b32',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
});
</script>

<?php
admin_render_end(true);