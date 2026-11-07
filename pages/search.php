
<?php
// Search keyword entered by the user
$searchTerm = trim($_GET['q'] ?? 'sports');

// Sample news articles (temporary data for the UI)
$news = [
    [
        "title" => "Sports Meet 2026",
        "category" => "Sports",
        "date" => "October 28, 2026",
        "description" => "Students showcase their talents and sportsmanship at the annual university sports meet.",
        "image" => "https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=800"
    ],
    [
        "title" => "Football Tournament",
        "category" => "Sports",
        "date" => "October 25, 2026",
        "description" => "University teams compete in an exciting inter-faculty football tournament.",
        "image" => "https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800"
    ],
    [
        "title" => "Basketball Championship",
        "category" => "Sports",
        "date" => "October 20, 2026",
        "description" => "The best basketball teams compete for the university championship.",
        "image" => "https://images.unsplash.com/photo-1546519638-68e109498ffc?w=800"
    ],
    [
        "title" => "New AI Research Initiative",
        "category" => "Academic",
        "date" => "October 18, 2026",
        "description" => "Students explore new developments in artificial intelligence.",
        "image" => "https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800"
    ]
];

// Filter articles based on the search keyword
$results = [];

if ($searchTerm !== "") {
    foreach ($news as $article) {
        $searchContent = $article['title'] . " " .
                         $article['category'] . " " .
                         $article['description'];

        if (stripos($searchContent, $searchTerm) !== false) {
            $results[] = $article;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Search Results | NSBM NewsDesk</title>

    <!-- Search page CSS -->
    <link rel="stylesheet" href="../assests/css/search.css">
</head>

<body>

<!-- Navigation Bar -->
<header class="navbar">
    <a href="home.php" class="logo">🍃 NSBM NewsDesk</a>

    <nav>
        <a href="home.php">Home</a>
        <a href="news.php">News</a>
        <a href="#">Academic</a>
        <a href="#">Sports</a>
        <a href="#">Events</a>
        <a href="#">Gallery</a>
        <a href="#">About</a>
    </nav>

    <form action="search.php" method="GET" class="nav-search">
        <input
            type="search"
            name="q"
            placeholder="Search news, events..."
            value="<?= htmlspecialchars($searchTerm) ?>"
        >
        <button type="submit">Search</button>
    </form>
</header>

<!-- Breaking News -->
<div class="breaking-news">
    <strong>✦ BREAKING NEWS</strong>
    <span>Stay updated with the latest news from NSBM Green University.</span>
</div>

<!-- Main Content -->
<main class="container">

    <section class="search-heading">
        <p class="small-heading">THE CAMPUS, CONNECTED</p>

        <h1>Search Results</h1>

        <p class="result-description">
            Showing results for:
            <strong>"<?= htmlspecialchars($searchTerm) ?>"</strong>
        </p>
    </section>

    <!-- Search Results -->
    <?php if (count($results) > 0): ?>

        <p class="result-count">
            <?= count($results) ?> article(s) found
        </p>

        <div class="news-grid">

            <?php foreach ($results as $article): ?>

                <article class="news-card">

                    <img
                        src="<?= htmlspecialchars($article['image']) ?>"
                        alt="<?= htmlspecialchars($article['title']) ?>"
                    >

                    <div class="card-content">

                        <span class="category">
                            <?= htmlspecialchars($article['category']) ?>
                        </span>

                        <h2>
                            <?= htmlspecialchars($article['title']) ?>
                        </h2>

                        <p>
                            <?= htmlspecialchars($article['description']) ?>
                        </p>

                        <div class="card-footer">
                            <span><?= htmlspecialchars($article['date']) ?></span>
                            <a href="news.php">Read More →</a>
                        </div>

                    </div>
                </article>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty-state">
            <div class="empty-icon">🔍</div>

            <h2>No news articles found</h2>

            <p>
                We couldn't find any articles matching
                "<?= htmlspecialchars($searchTerm) ?>".
                Try searching with another keyword.
            </p>

            <a href="search.php?q=sports" class="back-button">
                View Sports News
            </a>
        </div>

    <?php endif; ?>

</main>

<!-- Footer -->
<footer class="footer">
    <div>
        <h3>🍃 NSBM NewsDesk</h3>
        <p>
            Your window into life at NSBM Green University.
            Stories, achievements and moments from our community.
        </p>
        <small>© 2026 NSBM Green University. All rights reserved.</small>
    </div>
</footer>

</body>
</html>

