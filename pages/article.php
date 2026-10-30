
<?php
// Temporary sample news data
// Later, this will be connected to the database.

$newsArticles = [
    [
        "id" => 1,
        "title" => "NSBM Students Celebrate Annual Sports Meet",
        "category" => "Sports",
        "description" => "Students participated in various sporting events during the annual university sports meet.",
        "author" => "NewsDesk Team",
        "date" => "2026-10-15",
        "content" => "The annual sports meet brought together students from different faculties to participate in various sporting events. The event encouraged teamwork, sportsmanship and unity among the university community."
    ],
    [
        "id" => 2,
        "title" => "New Technology Workshop at NSBM",
        "category" => "Academic",
        "description" => "A technology workshop was organized to introduce students to modern technology trends.",
        "author" => "NewsDesk Team",
        "date" => "2026-10-12",
        "content" => "The technology workshop introduced students to modern technology trends and provided an opportunity to learn about new developments in the IT industry."
    ],
    [
        "id" => 3,
        "title" => "University Cultural Festival Announced",
        "category" => "Events",
        "description" => "NSBM announced its upcoming cultural festival with activities and performances.",
        "author" => "NewsDesk Team",
        "date" => "2026-10-18",
        "content" => "The university announced its upcoming cultural festival, featuring student performances and activities celebrating creativity and cultural diversity."
    ]
];

// Get the article ID from the URL
$articleId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Find the selected article
$selectedArticle = null;

foreach ($newsArticles as $article) {
    if ($article['id'] === $articleId) {
        $selectedArticle = $article;
        break;
    }
}

// Find related articles
$relatedArticles = [];

if ($selectedArticle !== null) {
    foreach ($newsArticles as $article) {
        if ($article['id'] !== $selectedArticle['id']) {
            $relatedArticles[] = $article;
        }
    }

    $relatedArticles = array_slice($relatedArticles, 0, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $selectedArticle
            ? htmlspecialchars($selectedArticle['title']) . " | NSBM NewsDesk"
            : "Article Not Found | NSBM NewsDesk" ?>
    </title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/news.css">
</head>

<body>

<header class="site-header">
    <div class="header-container">
        <a href="home.php" class="logo">NSBM NewsDesk</a>

        <nav class="main-nav">
            <a href="home.php">Home</a>
            <a href="news.php">News</a>
            <a href="search.php">Search</a>
        </nav>
    </div>
</header>

<main class="article-page">

    <?php if ($selectedArticle !== null): ?>

        <a href="news.php" class="back-link">← Back to News</a>

        <article class="article-content">

            <span class="article-category">
                <?= htmlspecialchars($selectedArticle['category']) ?>
            </span>

            <h1>
                <?= htmlspecialchars($selectedArticle['title']) ?>
            </h1>

            <div class="article-meta">
                <span>By <?= htmlspecialchars($selectedArticle['author']) ?></span>

                <span>
                    <?= date('d F Y', strtotime($selectedArticle['date'])) ?>
                </span>
            </div>

            <div class="article-image-placeholder">
                <span>News Article Image</span>
            </div>

            <p class="article-description">
                <?= htmlspecialchars($selectedArticle['description']) ?>
            </p>

            <div class="article-body">
                <p>
                    <?= htmlspecialchars($selectedArticle['content']) ?>
                </p>
            </div>

            <section class="bookmark-section">
                <h3>Save This Article</h3>
                <p>Bookmark functionality will be connected with the backend later.</p>
                <button type="button" disabled>Bookmark Article</button>
            </section>

            <section class="comments-section">
                <h2>Comments</h2>
                <p>Comments functionality will be available after backend integration.</p>
            </section>

        </article>

        <section class="related-articles">
            <h2>Related News</h2>

            <div class="related-news-grid">

                <?php foreach ($relatedArticles as $related): ?>

                    <div class="related-news-card">

                        <span>
                            <?= htmlspecialchars($related['category']) ?>
                        </span>

                        <h3>
                            <?= htmlspecialchars($related['title']) ?>
                        </h3>

                        <p>
                            <?= htmlspecialchars($related['description']) ?>
                        </p>

                        <a href="article.php?id=<?= $related['id'] ?>">
                            Read More →
                        </a>

                    </div>

                <?php endforeach; ?>

            </div>
        </section>

    <?php else: ?>

        <section class="article-not-found">
            <h1>Article Not Found</h1>
            <p>Sorry, the news article you are looking for is unavailable.</p>
            <a href="news.php">Back to News</a>
        </section>

    <?php endif; ?>

</main>

<footer class="site-footer">
    <p>&copy; 2026 NSBM NewsDesk. All Rights Reserved.</p>
</footer>

</body>
</html>
