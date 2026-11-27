<?php
require_once __DIR__ . '/../../backend/config/session.php';
require_once __DIR__ . '/../../backend/config/database.php';

if (
    empty($_SESSION['user_id']) ||
    ($_SESSION['user_role'] ?? '') !== 'admin'
) {
    header('Location: ../auth/login.php');
    exit;
}

$activePage = 'dashboard';
$loggedInUserName = $_SESSION['user_name'] ?? 'Admin User';
$firstLetter = strtoupper(substr($loggedInUserName, 0, 1));

// Database Dynamic Stats Queries
try {
    $totalNews = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn() ?: 0;
    $publishedThisMonth = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'published' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn() ?: 0;
    $activeCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() ?: 0;

    // Fetch 5 Most Recent Articles
    $recentStmt = $pdo->query("
        SELECT articles.*, users.name as author_name 
        FROM articles 
        LEFT JOIN users ON articles.author_id = users.id 
        ORDER BY articles.created_at DESC 
        LIMIT 5
    ");
    $recentArticles = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Categories Summary
    $catStmt = $pdo->query("
        SELECT categories.name, COUNT(articles.id) as article_count 
        FROM categories 
        LEFT JOIN articles ON categories.id = articles.category_id 
        GROUP BY categories.id 
        LIMIT 5
    ");
    $categoriesSummary = $catStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $totalNews = 0;
    $publishedThisMonth = 0;
    $activeCategories = 0;
    $recentArticles = [];
    $categoriesSummary = [];
}
// Additional dashboard analytics
$totalUsers = (int) $pdo->query(
    "SELECT COUNT(*) FROM users"
)->fetchColumn();

$totalComments = (int) $pdo->query(
    "SELECT COUNT(*) FROM comments"
)->fetchColumn();

$totalBookmarks = (int) $pdo->query(
    "SELECT COUNT(*) FROM bookmarks"
)->fetchColumn();

$totalReports = (int) $pdo->query(
    "SELECT COUNT(*) FROM content_reports"
)->fetchColumn();
$popularCategories = $pdo->query("
    SELECT c.name, COUNT(a.id) AS article_count
    FROM categories c
    LEFT JOIN articles a ON a.category_id = c.id
    GROUP BY c.id, c.name
    ORDER BY article_count DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$mostViewedArticles = $pdo->query("
    SELECT a.title, COUNT(av.id) AS view_count
    FROM articles a
    LEFT JOIN article_views av ON av.article_id = a.id
    GROUP BY a.id, a.title
    ORDER BY view_count DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NSBM NewsDesk</title>
    <link rel="stylesheet" href="/NSBM-NewsDesk/assets/css/admin.css?v=2.2">
    
    <!-- Anti-Flicker Script: Prevents sidebar expanding before collapse on tab switch -->
    <script>
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed');
            document.addEventListener('DOMContentLoaded', () => {
                document.body.classList.add('sidebar-collapsed');
            });
        }
    </script>
</head>
<body>

    <?php include '../../components/admin-sidebar.php'; ?>

    <main class="admin-main">
        <header class="top-bar">
            <div style="display: flex; align-items: center; gap: 12px;">
                <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
                <div class="page-title">
                    <h1>Admin Dashboard</h1>
                    <p>admin / dashboard.php</p>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 15px;">
                <div class="user-profile">
                    <div class="avatar"><?= htmlspecialchars($firstLetter) ?></div>
                    <span><?= htmlspecialchars($loggedInUserName) ?></span>
                </div>

                <!-- Secure POST Logout Form -->
                <form action="../../backend/auth/logout.php" method="POST" style="margin: 0;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 20px; padding: 6px 14px;">Logout</button>
                </form>
            </div>
        </header>

        <!-- Stats Section -->
        <section class="stats-grid">
            <div class="stat-card">
                <h3>Total News Articles</h3>
                <div class="number"><?= htmlspecialchars($totalNews) ?></div>
            </div>
            <div class="stat-card">
                <h3>Published This Month</h3>
                <div class="number"><?= htmlspecialchars($publishedThisMonth) ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Categories</h3>
                <div class="number"><?= htmlspecialchars($activeCategories) ?></div>
            </div>
        </section>
 
        <section class="data-card">
            <div class="card-header">
               <h2>Analytics</h2>
            </div>
<div class="analytics-content"></div>
    <h3>Platform Activity Overview</h3>

    <p>Total Users: <?= (int)($totalUsers ?? 0) ?></p>
    <p>Total News: <?= (int)($totalNews ?? 0) ?></p>
    <p>Total Comments: <?= (int)($totalComments ?? 0) ?></p>
    <p>Total Bookmarks: <?= (int)($totalBookmarks ?? 0) ?></p>
    <p>Total Reports: <?= (int)($totalReports ?? 0) ?></p> <div id="activityChart" class="activity-chart"></div>
</div>
        </section>

        <!-- Dynamic Tables Grid -->
        <div class="dashboard-grid">
            <!-- Recent News Table -->
            <div class="data-card">
                <div class="card-header">
                    <h2>Recent News Management</h2>
                    <a href="news.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Status</th>
                                <th>Created Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentArticles)): ?>
                                <?php foreach ($recentArticles as $article): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($article['title']) ?></strong></td>
                                        <td><?= htmlspecialchars($article['author_name'] ?? 'System Admin') ?></td>
                                        <td>
                                            <span class="badge badge-<?= strtolower($article['status']) ?>">
                                                <?= htmlspecialchars(ucfirst($article['status'])) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d M Y', strtotime($article['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4">No recent articles found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Categories Summary Table -->
            <div class="data-card">
                <div class="card-header">
                    <h2>Categories Summary</h2>
                    <a href="categories.php" class="btn btn-primary btn-sm">Manage</a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($categoriesSummary)): ?>
                                <?php foreach ($categoriesSummary as $cat): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($cat['article_count']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2">No categories available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><div class="dashboard-grid">

    <div class="data-card">
        <div class="card-header">
            <h2>Popular Categories</h2>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Articles</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($popularCategories as $category): ?>
                <tr>
                    <td><?= htmlspecialchars($category['name']) ?></td>
                    <td><?= (int)$category['article_count'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="data-card">
        <div class="card-header">
            <h2>Most Viewed Articles</h2>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Article Title</th>
                    <th>Views</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mostViewedArticles as $article): ?>
                <tr>
                    <td><?= htmlspecialchars($article['title']) ?></td>
                    <td><?= (int)$article['view_count'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
    </main>

    <script src="../../assets/js/admin.js"></script>
    <style>
.activity-chart {
    display: flex;
    align-items: flex-end;
    justify-content: space-around;
    gap: 15px;
    height: 230px;
    padding: 20px;
    margin-top: 20px;
    border-top: 1px solid #ddd;
}

.activity-bar {
    flex: 1;
    max-width: 120px;
    text-align: center;
}

.activity-bar-fill {
    background: #16834b;
    border-radius: 8px 8px 0 0;
    min-height: 3px;
}

.activity-bar-label {
    margin-top: 10px;
    font-size: 13px;
}
</style>

<script>
const activityData = {
    Users: <?= (int)$totalUsers ?>,
    News: <?= (int)$totalNews ?>,
    Comments: <?= (int)$totalComments ?>,
    Bookmarks: <?= (int)$totalBookmarks ?>,
    Reports: <?= (int)$totalReports ?>
};

const activityChart = document.getElementById('activityChart');

if (activityChart) {
    const maxValue = Math.max(1, ...Object.values(activityData));

    Object.entries(activityData).forEach(([label, value]) => {
        const bar = document.createElement('div');
        bar.className = 'activity-bar';

        const fill = document.createElement('div');
        fill.className = 'activity-bar-fill';
        fill.style.height = `${Math.max(3, (value / maxValue) * 150)}px`;

        const count = document.createElement('div');
        count.textContent = value;

        const name = document.createElement('div');
        name.className = 'activity-bar-label';
        name.textContent = label;

        bar.append(count, fill, name);
        activityChart.appendChild(bar);
    });
}
</script>
</body>
</html>