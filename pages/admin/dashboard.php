<?php
declare(strict_types=1);

require_once __DIR__ . '/../../backend/config/session.php';
require_once __DIR__ . '/../../backend/config/database.php';

// Enforce Admin Authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Generate CSRF token if missing (for secure logout)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$activePage = 'dashboard';
$loggedInUserName = $_SESSION['user_name'] ?? 'Admin User';
$firstLetter = strtoupper(substr($loggedInUserName, 0, 1));

// Fetch Dashboard Statistics, Analytics & Summaries
try {
    $totalNews = (int) $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
    
    $publishedThisMonth = (int) $pdo->query("
        SELECT COUNT(*) FROM articles 
        WHERE status = 'published' 
          AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
          AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ")->fetchColumn();

    $activeCategories = (int) $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

    // Platform Activity Analytics (Merged from dev branch)
    $totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalComments = (int) $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
    $totalBookmarks = (int) $pdo->query("SELECT COUNT(*) FROM bookmarks")->fetchColumn();
    $totalReports = (int) $pdo->query("SELECT COUNT(*) FROM content_reports")->fetchColumn();

    // Fetch 5 Most Recent Articles
    $recentStmt = $pdo->query("
        SELECT a.id, a.title, a.status, a.created_at, u.name AS author_name
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 5
    ");
    $recentArticles = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Categories with Article Counts
    $catStmt = $pdo->query("
        SELECT c.name, COUNT(a.id) AS article_count
        FROM categories c
        LEFT JOIN articles a ON c.id = a.category_id
        GROUP BY c.id, c.name
        ORDER BY c.name ASC
    ");
    $categoriesSummary = $catStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Dashboard query error: ' . $e->getMessage());
    $totalNews = 0;
    $publishedThisMonth = 0;
    $activeCategories = 0;
    $totalUsers = 0;
    $totalComments = 0;
    $totalBookmarks = 0;
    $totalReports = 0;
    $recentArticles = [];
    $categoriesSummary = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | NSBM NewsDesk Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin.css?v=8">
    <script>
        if (localStorage.getItem('nsbm-admin-sidebar-collapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
    <style>
        .analytics-metrics {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 12px;
        }
        .metric-pill {
            background: #eef5f1;
            color: #09482e;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }
        .activity-chart {
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            gap: 24px;
            height: 220px;
            padding: 24px 32px 16px;
            margin-top: 14px;
            border-top: 1px solid #e5e7eb;
            background-image: linear-gradient(to bottom, #f3f4f6 1px, transparent 1px);
            background-size: 100% 44px;
        }
        .activity-bar {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 64px;
            text-align: center;
        }
        .activity-bar-fill {
            width: 44px;
            background: linear-gradient(180deg, #0d6340 0%, #09482e 100%);
            border-radius: 6px 6px 0 0;
            min-height: 4px;
            margin-top: 6px;
            transition: height 0.3s ease;
        }
        .activity-bar-label {
            margin-top: 10px;
            font-size: 13px;
            color: #4b5563;
            font-weight: 600;
        }
        .activity-bar-fill {
            background: #09482e;
            border-radius: 8px 8px 0 0;
            min-height: 4px;
            margin-top: 6px;
        }
        .activity-bar-label {
            margin-top: 10px;
            font-size: 13px;
            color: #4b5563;
            font-weight: 500;
        }
    </style>
</head>
<body>

    <!-- Left Sidebar -->
    <?php include '../../components/admin-sidebar.php'; ?>

    <!-- Main Workspace -->
    <main class="admin-main">
        <!-- Top Header Bar -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Menu">☰</button>
                <h1>Dashboard</h1>
            </div>
            <div class="top-bar-actions">
                <div class="avatar-circle" title="<?= htmlspecialchars($loggedInUserName, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($firstLetter, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <a href="../home.php" class="btn btn-outline">View Site</a>
                <form action="../../backend/auth/logout.php" method="POST" style="margin:0;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </header>

        <div class="content-wrapper">
            <!-- Top KPI Summary Cards -->
            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $totalNews ?></div>
                    <div class="stat-label">Total News Articles</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $publishedThisMonth ?></div>
                    <div class="stat-label">Published This Month</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $activeCategories ?></div>
                    <div class="stat-label">Active Categories</div>
                </div>
            </section>

            <!-- Platform Activity Analytics (Merged with Admin Panel Card UI) -->
            <section class="panel-card" id="analyticsContainer">
                <div class="panel-header">
                    <h2>Platform Activity Overview</h2>
                </div>
                <div class="analytics-metrics">
                    <span class="metric-pill">Total Users: <?= $totalUsers ?></span>
                    <span class="metric-pill">Total News: <?= $totalNews ?></span>
                    <span class="metric-pill">Total Comments: <?= $totalComments ?></span>
                    <span class="metric-pill">Total Bookmarks: <?= $totalBookmarks ?></span>
                    <span class="metric-pill">Total Reports: <?= $totalReports ?></span>
                </div>
                <div id="activityChart" class="activity-chart"></div>
            </section>

            <!-- Bottom Two-Column Overview Grid -->
            <section class="two-col-grid">
                <!-- Recent News Table -->
                <div class="panel-card" style="margin-bottom: 0;">
                    <div class="panel-header">
                        <h2>Recent News Management</h2>
                        <a href="news.php" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    <div class="table-wrapper" style="border: none;">
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
                                    <?php foreach ($recentArticles as $article): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                            <td><?= htmlspecialchars($article['author_name'] ?? 'Admin Office', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <span class="badge badge-<?= strtolower($article['status']) === 'published' ? 'published' : 'draft' ?>">
                                                    <?= htmlspecialchars(ucfirst($article['status']), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td><?= date('d M Y', strtotime($article['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4">No recent news articles found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Categories Summary Table -->
                <div class="panel-card" style="margin-bottom: 0;">
                    <div class="panel-header">
                        <h2>Categories Summary</h2>
                        <a href="categories.php" class="btn btn-outline btn-sm">Manage</a>
                    </div>
                    <div class="table-wrapper" style="border: none;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Articles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($categoriesSummary)): ?>
                                    <?php foreach ($categoriesSummary as $cat): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                            <td><span class="count-pill"><?= (int) $cat['article_count'] ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="2">No categories created yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script src="../../assets/js/admin.js?v=6"></script>
    <script>
        const activityData = {
            Users: <?= $totalUsers ?>,
            News: <?= $totalNews ?>,
            Comments: <?= $totalComments ?>,
            Bookmarks: <?= $totalBookmarks ?>,
            Reports: <?= $totalReports ?>
        };

        const activityChart = document.getElementById('activityChart');

        if (activityChart) {
            const maxValue = Math.max(1, ...Object.values(activityData));

            Object.entries(activityData).forEach(([label, value]) => {
                const bar = document.createElement('div');
                bar.className = 'activity-bar';

                const fill = document.createElement('div');
                fill.className = 'activity-bar-fill';
                fill.style.height = `${Math.max(4, (value / maxValue) * 150)}px`;

                const count = document.createElement('div');
                count.style.fontWeight = '700';
                count.style.fontSize = '13px';
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