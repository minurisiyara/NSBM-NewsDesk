
<?php
// --------------------------------------------------
// NSBM NewsDesk - News Browsing Page
// Demo content only. No database connection is used.
// --------------------------------------------------

// Sample news articles for frontend development.
$newsArticles = [
    [
        "id" => 1,
        "title" => "NSBM Students Participate in Technology Exhibition",
        "category" => "Academic",
        "date" => "2026-10-20",
        "image" => "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800",
        "description" => "Students explore technology, innovation, and new learning opportunities."
    ],
    [
        "id" => 2,
        "title" => "University Sports Meet Celebrates Student Talent",
        "category" => "Sports",
        "date" => "2026-10-18",
        "image" => "https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=800",
        "description" => "Students come together to celebrate teamwork and sporting achievements."
    ],
    [
        "id" => 3,
        "title" => "Campus Career Guidance Session Announced",
        "category" => "Events",
        "date" => "2026-10-15",
        "image" => "https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=800",
        "description" => "A sample announcement about career guidance and professional development."
    ],
    [
        "id" => 4,
        "title" => "Computing Students Showcase Their Projects",
        "category" => "Technology",
        "date" => "2026-10-12",
        "image" => "https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=800",
        "description" => "Explore examples of teamwork, programming, and computing projects."
    ],
    [
        "id" => 5,
        "title" => "Student Community Takes Part in Campus Activities",
        "category" => "Events",
        "date" => "2026-10-10",
        "image" => "https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=800",
        "description" => "A sample story highlighting student participation and campus life."
    ],
    [
        "id" => 6,
        "title" => "New Learning Opportunities for University Students",
        "category" => "Academic",
        "date" => "2026-10-08",
        "image" => "https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800",
        "description" => "A demonstration story about learning, education, and student development."
    ]
];

// Read the filters submitted through the URL.
$searchTerm = trim($_GET["q"] ?? "");
$selectedCategory = trim($_GET["category"] ?? "");
$selectedDate = trim($_GET["date"] ?? "");

// Keep only articles that match the selected filters.
$filteredArticles = array_filter(
    $newsArticles,
    function ($article) use ($searchTerm, $selectedCategory, $selectedDate) {
        $matchesSearch = $searchTerm === ""
            || stripos($article["title"], $searchTerm) !== false
            || stripos($article["description"], $searchTerm) !== false;

        $matchesCategory = $selectedCategory === ""
            || $article["category"] === $selectedCategory;

        $matchesDate = $selectedDate === ""
            || $article["date"] === $selectedDate;

        return $matchesSearch && $matchesCategory && $matchesDate;
    }
);

// Escape text before displaying it in HTML.
function newsEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Latest News | NSBM NewsDesk</title>

    <!-- Reuse the project's existing stylesheet. -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        /* News page-specific styles */
        .news-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 35px 20px 60px;
        }

        .news-page-heading {
            margin-bottom: 25px;
        }

        .news-page-heading h1 {
            margin-bottom: 8px;
            color: #185c42;
        }

        .demo-notice {
            padding: 12px 16px;
            margin-bottom: 24px;
            border-left: 4px solid #b88724;
            background: #fff8e8;
            color: #5c481b;
            border-radius: 4px;
        }

        .news-filters {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 12px;
            margin-bottom: 30px;
            padding: 18px;
            background: #f5f7f5;
            border-radius: 10px;
        }

        .news-filters input,
        .news-filters select {
            width: 100%;
            min-width: 0;
            padding: 11px;
            border: 1px solid #d5ddd7;
            border-radius: 6px;
            background: white;
            font: inherit;
        }

        .news-filter-button {
            padding: 11px 20px;
            border: none;
            border-radius: 6px;
            background: #185c42;
            color: white;
            cursor: pointer;
            font: inherit;
        }

        .news-filter-button:hover {
            background: #10452f;
        }

        .clear-filters {
            display: inline-block;
            margin-top: 12px;
            color: #185c42;
        }

        .news-results-heading {
            margin-bottom: 20px;
        }

        .news-results-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
        }

        .news-result-card {
            overflow: hidden;
            border: 1px solid #e3e8e4;
            border-radius: 10px;
            background: white;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
        }

        .news-result-card img {
            display: block;
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .news-result-content {
            padding: 18px;
        }

        .news-category {
            display: inline-block;
            margin-bottom: 10px;
            color: #185c42;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .news-result-content h2 {
            margin: 0 0 10px;
            font-size: 1.15rem;
            line-height: 1.5;
        }

        .news-result-content p {
            line-height: 1.6;
        }

        .news-date {
            color: #666;
            font-size: 0.9rem;
        }

        .read-news-link {
            display: inline-block;
            margin-top: 8px;
            color: #185c42;
            font-weight: bold;
            text-decoration: none;
        }

        .read-news-link:hover {
            text-decoration: underline;
        }

        .no-news-results {
            padding: 35px 20px;
            text-align: center;
            background: #f5f7f5;
            border-radius: 10px;
        }

        @media (max-width: 850px) {
            .news-filters {
                grid-template-columns: 1fr 1fr;
            }

            .news-results-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 550px) {
            .news-filters,
            .news-results-grid {
                grid-template-columns: 1fr;
            }

            .news-page {
                padding: 25px 15px 40px;
            }
        }
    </style>
</head>

<body>

<!-- Public website header -->
<header class="site-header">
    <div class="header-container">

        <a class="brand" href="home.php">
            <span>NSBM NewsDesk</span>
        </a>

        <nav class="main-nav" aria-label="Main navigation">
            <a href="home.php">Home</a>
            <a class="active" href="news.php">News</a>
            <a href="news.php?category=Academic">Academic</a>
            <a href="news.php?category=Sports">Sports</a>
            <a href="news.php?category=Events">Events</a>
        </nav>

        <div class="header-actions">
            <a class="login-link" href="../backend/auth/login.php">Login</a>
            <a class="signup-link" href="../backend/auth/register.php">Sign Up</a>
        </div>

    </div>
</header>

<main class="news-page">

    <section class="news-page-heading">
        <h1>Latest News</h1>
        <p>Explore news, events, and updates from our university community.</p>
    </section>

    <!-- Clearly identify temporary demonstration content. -->
    <div class="demo-notice">
        <strong>Development demo:</strong>
        These are sample articles for testing the page, not verified official NSBM news.
    </div>

    <!-- Search and filter form -->
    <form class="news-filters" action="news.php" method="get">

        <input
            type="search"
            name="q"
            placeholder="Search news..."
            aria-label="Search news"
            value="<?= newsEscape($searchTerm) ?>"
        >

        <select name="category" aria-label="Filter by category">
            <option value="">All categories</option>
            <?php foreach (["Academic", "Sports", "Events", "Technology"] as $category): ?>
                <option
                    value="<?= newsEscape($category) ?>"
                    <?= $selectedCategory === $category ? "selected" : "" ?>
                >
                    <?= newsEscape($category) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input
            type="date"
            name="date"
            aria-label="Filter by publication date"
            value="<?= newsEscape($selectedDate) ?>"
        >

        <button class="news-filter-button" type="submit">
            Search
        </button>

    </form>

    <?php if ($searchTerm !== "" || $selectedCategory !== "" || $selectedDate !== ""): ?>
        <a class="clear-filters" href="news.php">Clear all filters</a>
    <?php endif; ?>

    <section aria-labelledby="news-results-title">

        <h2 class="news-results-heading" id="news-results-title">
            News Articles (<?= count($filteredArticles) ?>)
        </h2>

        <?php if (count($filteredArticles) > 0): ?>

            <div class="news-results-grid">

                <?php foreach ($filteredArticles as $article): ?>

                    <article class="news-result-card">

                        <img
                            src="<?= newsEscape($article["image"]) ?>"
                            alt="Illustrative image for <?= newsEscape($article["title"]) ?>"
                            loading="lazy"
                        >

                        <div class="news-result-content">

                            <span class="news-category">
                                <?= newsEscape($article["category"]) ?>
                            </span>

                            <h2>
                                <?= newsEscape($article["title"]) ?>
                            </h2>

                            <p class="news-date">
                                Published:
                                <time datetime="<?= newsEscape($article["date"]) ?>">
                                    <?= date("d M Y", strtotime($article["date"])) ?>
                                </time>
                            </p>

                            <p>
                                <?= newsEscape($article["description"]) ?>
                            </p>

                            <!-- Article page will be implemented in a later step. -->
                            <a
                                class="read-news-link"
                                href="article.php?id=<?= (int)$article["id"] ?>"
                            >
                                Read More →
                            </a>

                        </div>
                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="no-news-results">
                <h2>No news found</h2>
                <p>Try a different keyword, category, or publication date.</p>
                <a class="read-news-link" href="news.php">View all news</a>
            </div>

        <?php endif; ?>

    </section>

</main>

<!-- Website footer -->
<footer class="site-footer" id="footer">
    <div class="footer-content">
        <h2>NSBM NewsDesk</h2>
        <p>Your demonstration university news portal.</p>
        <p>
            <a href="home.php">Home</a> |
            <a href="news.php">Latest News</a> |
            <a href="https://www.nsbm.ac.lk/">NSBM Official Website</a>
        </p>
        <p>&copy; <?= date("Y") ?> NSBM NewsDesk — Student project demo.</p>
    </div>
</footer>

</body>
</html>
