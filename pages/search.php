<?php
declare(strict_types=1);

require_once __DIR__ . '/../backend/config/database.php';

$query = trim($_GET['q'] ?? '');
$results = [];

try {
    if ($query !== '') {
        $like = '%' . $query . '%';
        $stmt = $pdo->prepare("
            SELECT a.id, a.title, a.summary, a.content, a.image_path,
                   a.created_at AS display_date,
                   COALESCE(c.name, 'General') AS category_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.status = 'published'
              AND (a.title LIKE :q1 OR a.summary LIKE :q2 OR a.content LIKE :q3 OR c.name LIKE :q4)
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([
            'q1' => $like,
            'q2' => $like,
            'q3' => $like,
            'q4' => $like
        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log('Search query error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search<?= $query !== '' ? ': ' . htmlspecialchars($query, ENT_QUOTES, 'UTF-8') : '' ?> | NSBM NewsDesk</title>
    <link rel="stylesheet" href="../assets/css/news.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=10">
</head>
<body>

<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="main-container">
    <section class="latest-section" style="margin-top: 24px;">
        <div class="section-heading latest-heading">
            <h2>
                <?= $query !== ''
                    ? 'Search Results for "' . htmlspecialchars($query, ENT_QUOTES, 'UTF-8') . '"'
                    : 'Search Campus News' ?>
            </h2>
            <?php if ($query !== ''): ?>
                <span><?= count($results) ?> match<?= count($results) === 1 ? '' : 'es' ?></span>
            <?php endif; ?>
        </div>

        <div class="latest-grid">
            <?php if ($query === ''): ?>
                <p style="color: #6b7280; font-size: 15px;">Type a keyword in the search bar above to find news articles, categories, or events.</p>
            <?php elseif (!empty($results)): ?>
                <?php foreach ($results as $article): ?>
                    <?php include __DIR__ . '/../components/new-card.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #6b7280; font-size: 15px;">No matching published articles found for "<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>".</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

</body>
</html>