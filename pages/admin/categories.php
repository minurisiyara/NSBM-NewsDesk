<?php
require_once '../../backend/config/database.php';
require_once '../../backend/config/session.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$activePage = 'categories';
$loggedInUserName = $_SESSION['user_name'] ?? 'Admin User';
$firstLetter = strtoupper(substr($loggedInUserName, 0, 1));

// Fetch stats & categories
$totalArticles = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn() ?: 0;
$publishedArticles = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'published'")->fetchColumn() ?: 0;
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() ?: 0;

$categories = $pdo->query("
    SELECT categories.*, COUNT(articles.id) AS article_count 
    FROM categories 
    LEFT JOIN articles ON categories.id = articles.category_id 
    GROUP BY categories.id 
    ORDER BY categories.name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Categories Management - NSBM NewsDesk</title>
    <link rel="stylesheet" href="../../assets/css/admin.css?v=3.0">
    <script>
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
</head>
<body>

    <?php include '../../components/admin-sidebar.php'; ?>

    <main class="admin-main">
        <header class="top-bar">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800;">Categories Management</h1>
            </div>
            <div style="display: flex; gap: 12px; align-items: center;">
                <span class="brand-badge">Admin</span>
                <div class="user-profile">
                    <span><?= htmlspecialchars($loggedInUserName) ?></span>
                </div>
            </div>
        </header>

        <!-- Stats Bar -->
        <section class="stats-grid">
            <div class="stat-card">
                <div>
                    <h3>Total Articles</h3>
                    <div class="number"><?= $totalArticles ?></div>
                </div>
                <svg class="sparkline" viewBox="0 0 100 30"><path d="M0 25 Q25 5, 50 15 T100 5" /></svg>
            </div>
            <div class="stat-card">
                <div>
                    <h3>Published</h3>
                    <div class="number"><?= $publishedArticles ?></div>
                </div>
                <svg class="sparkline" viewBox="0 0 100 30"><path d="M0 20 Q30 5, 60 25 T100 10" /></svg>
            </div>
            <div class="stat-card">
                <div>
                    <h3>Total Categories</h3>
                    <div class="number"><?= $totalCategories ?></div>
                </div>
                <svg class="sparkline" viewBox="0 0 100 30"><path d="M0 15 Q40 25, 70 5 T100 20" /></svg>
            </div>
        </section>

        <!-- Category Cards Grid -->
        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
                <div class="category-card">
                    <div>
                        <div class="cat-header">
                            <h3><?= htmlspecialchars($cat['name']) ?></h3>
                        </div>
                        <p><?= htmlspecialchars($cat['description'] ?? 'No description provided.') ?></p>
                    </div>
                    <div class="cat-footer">
                        <span class="count-badge">Active articles: <?= $cat['article_count'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script src="../../assets/js/admin.js"></script>
</body>
</html>