
<?php
// --------------------------------------------------
// NSBM NewsDesk - Search Results Page
// Uses demo articles only. No database connection.
// --------------------------------------------------

$articles = [
    1 => [
        "title" => "NSBM Students Participate in Technology Exhibition",
        "category" => "Academic",
        "date" => "2026-10-20",
        "image" => "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800",
        "description" => "Students explore technology, innovation, and new learning opportunities."
    ],
    2 => [
        "title" => "University Sports Meet Celebrates Student Talent",
        "category" => "Sports",
        "date" => "2026-10-18",
        "image" => "https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=800",
        "description" => "Students come together to celebrate teamwork and sporting achievements."
    ],
    3 => [
        "title" => "Campus Career Guidance Session Announced",
        "category" => "Events",
        "date" => "2026-10-15",
        "image" => "https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=800",
        "description" => "A sample announcement about career guidance and professional development."
    ],
    4 => [
        "title" => "Computing Students Showcase Their Projects",
        "category" => "Technology",
        "date" => "2026-10-12",
        "image" => "https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=800",
        "description" => "Explore examples of teamwork, programming, and computing projects."
    ],
    5 => [
        "title" => "Student Community Takes Part in Campus Activities",
        "category" => "Events",
        "date" => "2026-10-10",
        "image" => "https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=800",
        "description" => "A sample story highlighting student participation and campus life."
    ],
    6 => [
        "title" => "New Learning Opportunities for University Students",
        "category" => "Academic",
        "date" => "2026-10-08",
        "image" => "https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800",
        "description" => "A demonstration story about learning, education, and student development."
    ]
];

// Get the keyword from the homepage search form.
// Example: search.php?q=technology
$keyword = trim($_GET["q"] ?? "");
$selectedCategory = trim($_GET["category"] ?? "");

// Escape displayed text to help prevent HTML injection.
function searchEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Search titles, categories, and descriptions.
$results = [];

if ($keyword !== "" || $selectedCategory !== "") {
    foreach ($articles as $id => $article) {
        $searchText = $article["title"] . " "
            . $article["category"] . " "
            . $article["description"];

        $matchesKeyword = $keyword === ""
            || stripos($searchText, $keyword) !== false;

        $matchesCategory = $selectedCategory === ""
            || $article["category"] === $selectedCategory;

        if ($matchesKeyword && $matchesCategory) {
            $results[$id] = $article;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Search News | NSBM NewsDesk</title>

    <!-- Use the existing project stylesheet. -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .search-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 35px 20px 60px;
        }

        .search-heading h1 {
            color: #185c42;
            margin-bottom: 8px;
        }

        .search-demo-notice {
            margin: 22px 0;
            padding: 12px 16px;
            border-left: 4px solid #b88724;
            border-radius: 4px;
            background: #fff8e8;
            color: #5c481b;
        }

        .search-form {
            display: grid;
            grid-template-columns: 2fr 1fr auto;
            gap: 12px;
            margin: 25px 0;
            padding: 18px;
            border-radius: 10px;
            background: #f5f7f5;
        }

        .search-form input,
        .search-form select {
            width: 100%;
            min-width: 0;
            padding: 12px;
            border: 1px solid #d5ddd7;
            border-radius: 6px;
            background: white;
            font: inherit;
        }

        .search-submit {
            padding: 12px 22px;
            border: none;
            border-radius: 6px;
            background: #185c42;
            color: white;
            font: inherit;
            cursor: pointer;
        }

        .search-submit:hover {
            background: #10452f;
        }

        .search-clear {
            display: inline-block;
            margin: 0 0 20px;
            color: #185c42;
        }

        .search-results-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
        }

        .search-result-card {
            overflow: hidden;
            border: 1px solid #e3e8e4;
            border-radius: 10px;
            background: white;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
        }

        .search-result-card img {
            display: block;
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .search-result-content {
            padding: 18px;
        }

        .search-result-category {
            color: #185c42;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .search-result-content h2 {
            font-size: 1.15rem;
            line-height: 1.5;
        }

        .search-result-content p {
            line-height: 1.6;
        }

        .search-result-date {
            color: #666;
            font-size: 0.9rem;
        }

        .search-read-more {
            display: inline-block;
            color: #185c42;
            font-weight: bold;
            text-decoration: none;
        }

        .search-read-more:hover {
            text-decoration: underline;
        }

        .search-empty {
            padding: 35px 20px;
            border-radius: 10px;
            background: #f5f7f5;
            text-align: center;
        }

        @media (max-width: 850px) {
            .search-results-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .search-page {
                padding: 25px 15px 40px;
            }

            .search-form,
            .search-results-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<!-- Website header -->
<header class="site-header">
    <div class="header-container">

        <a class="brand" href="home.php">
            <span>NSBM NewsDesk</span>
        </a>

        <nav class="main-nav" aria-label="Main navigation">
            <a href="home.php">Home</a>
            <a href="news.php">News</a>
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

<main class="search-page">

    <section class="search-heading">
        <h1>Search News</h1>
        <p>Find news articles by keyword or category.</p>
    </section>

    <div class="search-demo-notice">
        <strong>Development demo:</strong>
        Results use sample articles, not verified official NSBM news.
    </div>

    <!-- Search form compatible with the homepage's q parameter. -->
    <form class="search-form" action="search.php" method="get">

        <input
            type="search"
            name="q"
            placeholder="Enter a keyword..."
            aria-label="Search news by keyword"
            value="<?= searchEscape($keyword) ?>"
        >

        <select name="category" aria-label="Filter by category">
            <option value="">All categories</option>

            <?php foreach (["Academic", "Sports", "Events", "Technology"] as $category): ?>
                <option
                    value="<?= searchEscape($category) ?>"
                    <?= $selectedCategory === $category ? "selected" : "" ?>
                >
                    <?= searchEscape($category) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="search-submit" type="submit">
            Search
        </button>

    </form>

    <?php if ($keyword !== "" || $selectedCategory !== ""): ?>

        <a class="search-clear" href="search.php">
            Clear search
        </a>

        <h2>
            <?= count($results) ?> result(s) found
            <?php if ($keyword !== ""): ?>
                for "<?= searchEscape($keyword) ?>"
            <?php endif; ?>
        </h2>

        <?php if (count($results) > 0): ?>

            <div class="search-results-grid">

                <?php foreach ($results as $id => $article): ?>

                    <article class="search-result-card">

                        <img
                            src="<?= searchEscape($article["image"]) ?>"
                            alt="Illustrative image for <?= searchEscape($article["title"]) ?>"
                            loading="lazy"
                        >

                        <div class="search-result-content">

                            <span class="search-result-category">
                                <?= searchEscape($article["category"]) ?>
                            </span>

                            <h2><?= searchEscape($article["title"]) ?></h2>

                            <p class="search-result-date">
                                Published:
                                <time datetime="<?= searchEscape($article["date"]) ?>">
                                    <?= date("d M Y", strtotime($article["date"])) ?>
                                </time>
                            </p>

                            <p><?= searchEscape($article["description"]) ?></p>

                            <a
                                class="search-read-more"
                                href="article.php?id=<?= (int)$id ?>"
                            >
                                Read More →
                            </a>

                        </div>
                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <section class="search-empty">
                <h2>No results found</h2>
                <p>Try another keyword or select a different category.</p>
                <a class="search-read-more" href="search.php">
                    View all news
                </a>
            </section>

        <?php endif; ?>

    <?php else: ?>

        <section class="search-empty">
            <h2>What would you like to find?</h2>
            <p>Enter a keyword or choose a category to search the demo articles.</p>
            <a class="search-read-more" href="news.php">
                Browse all news →
            </a>
        </section>

    <?php endif; ?>

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
