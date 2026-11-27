<?php
declare(strict_types=1);

require_once __DIR__ . '/../backend/config/database.php';

$selectedCategory = trim($_GET['category'] ?? '');
$categories = [];
$articles = [];

try {
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")
                      ->fetchAll(PDO::FETCH_ASSOC);

    if ($selectedCategory !== '') {
        $stmt = $pdo->prepare("
            SELECT a.id, a.title, a.summary, a.content, a.image_path,
                   a.created_at AS display_date,
                   COALESCE(c.name, 'General') AS category_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.status = 'published' AND LOWER(c.name) = LOWER(:category)
            ORDER BY a.created_at DESC
        ");
        $stmt->execute(['category' => $selectedCategory]);
    } else {
        $stmt = $pdo->query("
            SELECT a.id, a.title, a.summary, a.content, a.image_path,
                   a.created_at AS display_date,
                   COALESCE(c.name, 'General') AS category_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.status = 'published'
            ORDER BY a.created_at DESC
        ");
    }
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('News listing error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $selectedCategory !== '' ? htmlspecialchars($selectedCategory, ENT_QUOTES, 'UTF-8') . ' News' : 'All News' ?> | NSBM NewsDesk</title>
    <link rel="stylesheet" href="../assets/css/news.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=10">
    <style>
        .filter-pills { display: flex; flex-wrap: wrap; gap: 10px; margin: 20px 0 28px; }
        .filter-pill { padding: 8px 16px; border-radius: 999px; background: #ffffff; border: 1px solid #d1d5db; color: #374151; text-decoration: none; font-size: 14px; font-weight: 500; }
        .filter-pill.active, .filter-pill:hover { background: #09482e; color: #ffffff; border-color: #09482e; }
    </style>
</head>
<body>

<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="main-container">
    <section class="latest-section" style="margin-top: 24px;">
        <div class="section-heading latest-heading">
            <h2><?= $selectedCategory !== '' ? htmlspecialchars($selectedCategory, ENT_QUOTES, 'UTF-8') . ' Stories' : 'All Campus News' ?></h2>
            <span><?= count($articles) ?> article<?= count($articles) === 1 ? '' : 's' ?></span>
        </div>

        <div class="filter-pills">
            <a href="news.php" class="filter-pill <?= $selectedCategory === '' ? 'active' : '' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="news.php?category=<?= urlencode($cat['name']) ?>"
                   class="filter-pill <?= strcasecmp($selectedCategory, $cat['name']) === 0 ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="latest-grid">
            <?php if (!empty($articles)): ?>
                <?php foreach ($articles as $article): ?>
                    <?php include __DIR__ . '/../components/new-card.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #6b7280; font-size: 15px;">No published news articles found in this category.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

</body>
</html>
