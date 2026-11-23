<?php
declare(strict_types=1);

require_once '../../backend/config/session.php';
require_once '../../backend/config/database.php';

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

// Fetch Dashboard Statistics & Summaries
try {
    $totalNews = (int) $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
    
    $publishedThisMonth = (int) $pdo->query("
        SELECT COUNT(*) FROM articles 
        WHERE status = 'published' 
          AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
          AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ")->fetchColumn();

    $activeCategories = (int) $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

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
    <link rel="stylesheet" href="../../assets/css/admin.css?v=4">
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

            <!-- Reserved Analytics Component Container for Tharushika -->
            <section class="panel-card" id="analyticsContainer">
                <div class="panel-header">
                    <h2>Campus Traffic Analytics</h2>
                </div>
                <div style="padding: 36px 20px; text-align: center; background: #f9fafb; border-radius: 8px; border: 1px dashed #d1d5db; color: #6b7280;">
                    <p style="font-weight: 600; margin-bottom: 4px;">Analytics Component Reserved Area</p>
                    <small>Integration slot for Tharushika's traffic charts and engagement metrics.</small>
                </div>
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

    <script src="../../assets/js/admin.js"></script>
</body>
</html>