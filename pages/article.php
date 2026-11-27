<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../backend/config/database.php';

$articleId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$article = null;
$relatedArticles = [];

if ($articleId > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT a.*,
                   COALESCE(c.name, 'General') AS category_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.id = :id AND a.status = 'published'
            LIMIT 1
        ");
        $stmt->execute(['id' => $articleId]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($article) {
            $relStmt = $pdo->prepare("
                SELECT a.id, a.title, a.summary, a.content, a.image_path,
                       a.created_at AS display_date,
                       COALESCE(c.name, 'General') AS category_name
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.id
                WHERE a.status = 'published'
                  AND a.category_id = :cat_id
                  AND a.id != :id
                ORDER BY a.created_at DESC
                LIMIT 3
            ");
            $relStmt->execute([
                'cat_id' => $article['category_id'],
                'id'     => $articleId
            ]);
            $relatedArticles = $relStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log('Article read query error: ' . $e->getMessage());
    }
}

$authorName = 'NSBM Editorial Team';
if (!empty($article['author_id'])) {
    try {
        $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = :uid LIMIT 1");
        $userStmt->execute(['uid' => (int) $article['author_id']]);
        $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);
        if ($userRow) {
            $authorName = $userRow['full_name'] ?? $userRow['name'] ?? $userRow['username'] ?? $userRow['email'] ?? 'NSBM Editorial Team';
        }
    } catch (PDOException $e) {
        error_log('Author lookup error: ' . $e->getMessage());
    }
}

$authorInitial = strtoupper(substr(trim($authorName), 0, 1)) ?: 'N';

$fallbackImg = 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=1200';
$rawImg = trim((string) ($article['image_path'] ?? $article['image'] ?? ''));
if ($rawImg === '') {
    $heroImg = $fallbackImg;
} elseif (strpos($rawImg, 'http://') === 0 || strpos($rawImg, 'https://') === 0 || strpos($rawImg, '../') === 0 || strpos($rawImg, '/') === 0) {
    $heroImg = $rawImg;
} else {
    $heroImg = '../' . ltrim($rawImg, '/');
}

$wordCount = str_word_count(strip_tags((string) ($article['content'] ?? '')));
$readTime = max(1, (int) ceil($wordCount / 200)) . ' min read';
$articleSummary = trim((string) ($article['summary'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $article ? htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') : 'Article Not Found' ?> | NSBM NewsDesk</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=9">
    <link rel="stylesheet" href="../assets/css/article.css?v=2">
        <link rel="stylesheet" href="../assets/css/style.css?v=9">
    <link rel="stylesheet" href="../assets/css/article.css?v=2">
    <link rel="stylesheet" href="../assets/css/article-page.css?v=1">
</head>
<body>

<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="main-container">
    <?php if ($article): ?>
        <div class="article-page-container">
            <nav class="article-breadcrumb">
                <a href="home.php">Home</a>
                <span>/</span>
                <a href="news.php">News</a>
                <span>/</span>
                <a href="news.php?category=<?= urlencode($article['category_name']) ?>">
                    <?= htmlspecialchars($article['category_name'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </nav>

            <article class="article-wrapper">
                <span class="news-category"><?= htmlspecialchars($article['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
                <h1 class="article-title"><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h1>

                <div class="article-meta">
                    <div class="author-pill">
                        <span class="author-avatar"><?= htmlspecialchars($authorInitial, ENT_QUOTES, 'UTF-8') ?></span>
                        <span><?= htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <span class="meta-dot">•</span>
                    <span><?= date('F j, Y', strtotime($article['created_at'])) ?></span>
                    <span class="meta-dot">•</span>
                    <span><?= htmlspecialchars($readTime, ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <figure class="article-hero-frame">
                    <img src="<?= htmlspecialchars($heroImg, ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?>"
                         class="article-hero-img">
                </figure>

                <?php if ($articleSummary !== ''): ?>
                    <div class="article-lead">
                        <?= htmlspecialchars($articleSummary, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <div class="article-body">
                    <?php
                        $paragraphs = explode("\n", trim((string) $article['content']));
                        foreach ($paragraphs as $para) {
                            $cleanPara = trim($para);
                            if ($cleanPara !== '') {
                                echo '<p>' . htmlspecialchars($cleanPara, ENT_QUOTES, 'UTF-8') . '</p>';
                            }
                        }
                    ?>
                </div>

                <div class="article-actions-bar">
                    <a href="news.php" class="btn-outline">← Back to All News</a>
                    <div class="article-tags">
                        <span>Category:</span>
                        <a href="news.php?category=<?= urlencode($article['category_name']) ?>" class="article-tag-badge">
                            <?= htmlspecialchars($article['category_name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </div>
                </div>
            </article>
        </div>

        <?php if (!empty($relatedArticles)): ?>
            <section class="related-section">
                <div class="section-heading">
                    <h2>Related Stories</h2>
                    <a href="news.php?category=<?= urlencode($article['category_name']) ?>">More in <?= htmlspecialchars($article['category_name'], ENT_QUOTES, 'UTF-8') ?> →</a>
                </div>
                <div class="latest-grid">
                    <?php foreach ($relatedArticles as $article): ?>
                        <?php include __DIR__ . '/../components/new-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    <?php else: ?>
        <div class="not-found-box">
            <div class="not-found-icon">!</div>
            <h1>Article Not Found</h1>
            <p>Sorry, the article you are looking for is unavailable or may have been removed.</p>
            <a href="news.php" class="btn">Back to News</a>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>