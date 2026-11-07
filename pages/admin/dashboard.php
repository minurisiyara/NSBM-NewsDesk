<?php
require_once '../../backend/config/database.php';
require_once '../../backend/config/session.php';

// Session Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NSBM NewsDesk</title>
    <link rel="stylesheet" href="../../assets/css/admin.css?v=2.1">
    
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

        <!-- Analytics Reserved Area for Tharushika -->
        <section class="data-card">
            <div class="card-header">
                <h2>Analytics</h2>
            </div>
            <div class="analytics-placeholder">
                <p>Section Reserved for Tharushika's Analytics Components.</p>
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
        </div>
    </main>

    <script src="../../assets/js/admin.js"></script>
</body>
</html>