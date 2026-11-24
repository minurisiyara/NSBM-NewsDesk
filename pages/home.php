<?php
declare(strict_types=1);

require_once __DIR__ . '/../backend/config/database.php';

function estimateReadingTime(?string $content): string {
    $wordCount = str_word_count(strip_tags((string) $content));
    $minutes = max(1, (int) ceil($wordCount / 200));
    return $minutes . ' min read';
}

function resolveArticleImage(?string $imagePath, string $fallback): string {
    $path = trim((string) $imagePath);
    if ($path === '') {
        return $fallback;
    }
    if (preg_match('#^https?://#i', $path) || str_starts_with($path, '../') || str_starts_with($path, '/')) {
        return $path;
    }
    return '../' . ltrim($path, '/');
}

$defaultHeroImg = 'https://images.unsplash.com/photo-1562774053-701939374585?w=1200';
$breakingNewsList = [];
$featuredArticles = [];
$latestArticles = [];
$announcements = [];

try {
    $breakingStmt = $pdo->query("
        SELECT id, title
        FROM articles
        WHERE status = 'published' AND is_breaking = 1
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $breakingNewsList = $breakingStmt->fetchAll(PDO::FETCH_ASSOC);

    $featuredStmt = $pdo->query("
        SELECT a.id, a.title, a.content, a.image_path,
               a.created_at AS display_date,
               COALESCE(c.name, 'Academic') AS category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.status = 'published'
        ORDER BY a.is_featured DESC, a.created_at DESC
        LIMIT 5
    ");
    $featuredArticles = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);

    $featuredIds = array_column($featuredArticles, 'id');
    $excludeClause = !empty($featuredIds)
        ? 'AND a.id NOT IN (' . implode(',', array_map('intval', $featuredIds)) . ')'
        : '';

    $latestStmt = $pdo->query("
        SELECT a.id, a.title, a.summary, a.content, a.image_path,
               a.created_at AS display_date,
               COALESCE(c.name, 'General') AS category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.status = 'published' {$excludeClause}
        ORDER BY a.created_at DESC
        LIMIT 6
    ");
    $latestArticles = $latestStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Homepage articles query error: ' . $e->getMessage());
}

try {
    $annStmt = $pdo->query("
        SELECT id, title, content, created_at
        FROM announcements
        WHERE is_active = 1
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $announcements = $annStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Homepage announcements query error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | NSBM NewsDesk</title>
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/home-slider.css?v=2">
    <link rel="stylesheet" href="../assets/css/style.css?v=6">
</head>
<body>

<?php include __DIR__ . '/../components/navbar.php'; ?>

<section class="breaking-news">
    <strong>✦ BREAKING NEWS</strong>
    <div class="breaking-ticker-wrap">
        <div class="breaking-ticker-track">
            <?php if (!empty($breakingNewsList)): ?>
                <?php for ($loop = 0; $loop < 2; $loop++): ?>
                    <?php foreach ($breakingNewsList as $bNews): ?>
                        <a href="article.php?id=<?= (int) $bNews['id'] ?>" class="breaking-ticker-item">
                            <?= htmlspecialchars($bNews['title'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <span class="ticker-sep">•</span>
                    <?php endforeach; ?>
                <?php endfor; ?>
            <?php else: ?>
                <span class="breaking-ticker-item">NSBM achieves top 10 in Sri Lanka's private university rankings...</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<main class="main-container">
    <div class="welcome-row">
        <p>THE CAMPUS, CONNECTED.</p>
        <span><?= date('l, F j, Y') ?></span>
    </div>

    <section class="featured-layout">
        <div class="featured-slideshow" id="featuredSlideshow">
            <?php if (!empty($featuredArticles)): ?>
                <?php foreach ($featuredArticles as $index => $slide): ?>
                    <?php
                        $heroImage = resolveArticleImage($slide['image_path'] ?? '', $defaultHeroImg);
                        $heroDate = date('M j, Y', strtotime($slide['display_date']));
                        $heroReadTime = estimateReadingTime($slide['content'] ?? '');
                    ?>
                    <div class="featured-slide <?= $index === 0 ? 'is-active' : '' ?>"
                         style="background-image: url('<?= htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8') ?>');">
                        <div class="featured-content">
                            <span class="category-label"><?= htmlspecialchars($slide['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <h1><?= htmlspecialchars($slide['title'], ENT_QUOTES, 'UTF-8') ?></h1>
                            <p><?= htmlspecialchars($heroDate, ENT_QUOTES, 'UTF-8') ?> • <?= htmlspecialchars($heroReadTime, ENT_QUOTES, 'UTF-8') ?></p>
                            <a href="article.php?id=<?= (int) $slide['id'] ?>" class="read-story-btn">Read Full Story →</a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (count($featuredArticles) > 1): ?>
                    <button type="button" class="slide-nav-btn slide-prev" id="prevFeaturedBtn" aria-label="Previous slide">‹</button>
                    <button type="button" class="slide-nav-btn slide-next" id="nextFeaturedBtn" aria-label="Next slide">›</button>

                    <div class="slide-dots" id="featuredDots">
                        <?php foreach ($featuredArticles as $index => $slide): ?>
                            <button type="button"
                                    class="slide-dot <?= $index === 0 ? 'is-active' : '' ?>"
                                    data-slide="<?= $index ?>"
                                    aria-label="Go to slide <?= $index + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="featured-slide is-active" style="background-image: url('<?= $defaultHeroImg ?>');">
                    <div class="featured-content">
                        <span class="category-label">Academic</span>
                        <h1>NSBM Ranked Among Top 10 Private Universities in Sri Lanka</h1>
                        <p><?= date('M j, Y') ?> • 3 min read</p>
                        <a href="news.php" class="read-story-btn">Read Full Story →</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <aside class="announcements">
            <div class="section-heading">
                <h2>Announcements</h2>
                <a href="news.php">View All</a>
            </div>

            <?php if (!empty($announcements)): ?>
                <?php foreach ($announcements as $announcement): ?>
                    <?php
                        $ts = strtotime($announcement['created_at']);
                        $day = date('d', $ts);
                        $month = strtoupper(date('M', $ts));
                        $details = mb_strimwidth(strip_tags((string) $announcement['content']), 0, 50, '...');
                    ?>
                    <div class="announcement-item">
                        <div class="date-box">
                            <strong><?= htmlspecialchars($day, ENT_QUOTES, 'UTF-8') ?></strong>
                            <span><?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="announcement-info">
                            <h3><?= htmlspecialchars($announcement['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars($details, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #6b7280; font-size: 14px;">No active announcements right now.</p>
            <?php endif; ?>
        </aside>
    </section>

    <section class="latest-section">
        <div class="section-heading latest-heading">
            <h2>Latest News</h2>
            <a href="news.php">View All News →</a>
        </div>

        <div class="latest-grid">
            <?php if (!empty($latestArticles)): ?>
                <?php foreach ($latestArticles as $article): ?>
                    <?php include __DIR__ . '/../components/new-card.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #6b7280; font-size: 14px;">No published news articles available yet.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="../assets/js/home-slider.js?v=2"></script>
</body>
</html>