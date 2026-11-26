<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/components/admin-layout.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

$totalArticles = 0;
$publishedMonth = 0;
$activeCategories = 0;
$recentArticles = [];
$categoryCounts = [];
$dbError = '';

try {
    $totalArticles = (int) $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM articles WHERE LOWER(status) IN ('published','approved') AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
    $publishedMonth = (int) $stmt->fetchColumn();
} catch (Throwable $e) {
    $publishedMonth = $totalArticles;
}

try {
    $activeCategories = (int) $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $categoryCounts = $pdo->query("
        SELECT c.name, COUNT(a.id) AS article_count
        FROM categories c
        LEFT JOIN articles a ON a.category_id = c.id
        GROUP BY c.id, c.name
        ORDER BY c.name ASC
    ")->fetchAll();
} catch (Throwable $e) {
    $categoryCounts = [];
}

try {
    $recentArticles = $pdo->query("
        SELECT a.title, a.status, a.created_at, COALESCE(u.full_name, u.name, 'Author') AS author_name
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 5
    ")->fetchAll();
} catch (Throwable $e) {
    try {
        $recentArticles = $pdo->query("SELECT title, status, created_at, 'Author' AS author_name FROM articles ORDER BY id DESC LIMIT 5")->fetchAll();
    } catch (Throwable $e2) {
        $recentArticles = [];
    }
}

admin_render_start('dashboard.php', 'Dashboard');
?>

<?php if ($dbError !== ''): ?>
    <div class="alert">Database query notice: <?= admin_h($dbError) ?></div>
<?php endif; ?>

<div class="stats">
    <div class="card stat">
        <b><?= $totalArticles ?></b>
        <span>Total News Articles</span>
    </div>
    <div class="card stat">
        <b><?= $publishedMonth ?></b>
        <span>Published This Month</span>
    </div>
    <div class="card stat">
        <b><?= $activeCategories ?></b>
        <span>Active Categories</span>
    </div>
</div>

<div class="two">
    <div class="card pad">
        <h3 style="font-family:Inter;margin-bottom:10px">Recent News Management</h3>
        <div class="tw">
            <table>
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($recentArticles)): ?>
                    <?php foreach ($recentArticles as $row): ?>
                        <?php $st = (string) ($row['status'] ?? 'Draft'); ?>
                        <tr>
                            <td><?= admin_h($row['title'] ?? '') ?></td>
                            <td><?= admin_h($row['author_name'] ?? 'Author') ?></td>
                            <td>
                                <span class="pill <?= strtolower($st) === 'published' ? 'ok' : 'am' ?>">
                                    <?= admin_h(ucfirst($st)) ?>
                                </span>
                            </td>
                            <td><?= admin_h(!empty($row['created_at']) ? date('d M Y', strtotime((string) $row['created_at'])) : '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="meta">No recent articles found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card pad">
        <h3 style="font-family:Inter;margin-bottom:10px">Categories Summary</h3>
        <div class="tw">
            <table>
                <thead>
                <tr>
                    <th>Category</th>
                    <th>Articles</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($categoryCounts)): ?>
                    <?php foreach ($categoryCounts as $c): ?>
                        <tr>
                            <td><b><?= admin_h($c['name'] ?? '') ?></b></td>
                            <td><span class="pill cnt"><?= (int) ($c['article_count'] ?? 0) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2" class="meta">No categories available.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php admin_render_end(false); ?>