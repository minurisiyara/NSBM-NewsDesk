<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/components/admin-layout.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

$usersTotal = 0;
$articlesTotal = 0;
$commentsTotal = 0;
$catLabels = [];
$catCounts = [];

try { $usersTotal = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(); } catch (Throwable $e) {}
try { $articlesTotal = (int) $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn(); } catch (Throwable $e) {}
try { $commentsTotal = (int) $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn(); } catch (Throwable $e) {}

try {
    $rows = $pdo->query("
        SELECT c.name, COUNT(a.id) AS cnt
        FROM categories c
        LEFT JOIN articles a ON a.category_id = c.id
        GROUP BY c.id, c.name
    ")->fetchAll();
    foreach ($rows as $r) {
        $catLabels[] = (string) ($r['name'] ?? '');
        $catCounts[] = (int) ($r['cnt'] ?? 0);
    }
} catch (Throwable $e) {}

admin_render_start('reports.php', 'Reports');
?>
<div class="stats">
    <div class="card stat"><b><?= $articlesTotal ?></b><span>Total Articles</span></div>
    <div class="card stat"><b><?= $commentsTotal ?></b><span>Total Comments</span></div>
    <div class="card stat"><b><?= $usersTotal ?></b><span>Registered Users</span></div>
</div>

<div class="two" style="grid-template-columns:1fr 1.4fr;margin-top:0">
    <div class="card pad">
        <h3 style="font-family:Inter;margin-bottom:10px">Articles by Category</h3>
        <div style="position:relative;height:260px"><canvas id="categoryChart"></canvas></div>
    </div>
    <div class="card pad">
        <h3 style="font-family:Inter;margin-bottom:10px">Monthly Overview</h3>
        <div style="position:relative;height:260px"><canvas id="monthlyChart"></canvas></div>
    </div>
</div>

<script>
window.ADMIN_REPORT_DATA = {
    categoryLabels: <?= json_encode($catLabels, JSON_UNESCAPED_UNICODE) ?>,
    categoryCounts: <?= json_encode($catCounts) ?>,
    monthlyCounts: [2, 4, 3, 5, 6, <?= $articlesTotal ?>]
};
</script>
<?php admin_render_end(true); ?>