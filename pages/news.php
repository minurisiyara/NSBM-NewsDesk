
<?php
// ==========================================
// TEMPORARY SAMPLE NEWS DATA
// Later, this will be replaced with published
// news articles retrieved from the database.
// ==========================================

$newsArticles = [
    [
        "id" => 1,
        "title" => "NSBM Students Celebrate Annual Sports Meet",
        "category" => "Sports",
        "description" => "Students participated in various sporting events during the annual university sports meet.",
        "author" => "NewsDesk Team",
        "date" => "2026-10-15"
    ],
    [
        "id" => 2,
        "title" => "New Technology Workshop at NSBM",
        "category" => "Academic",
        "description" => "An informative workshop introduced students to modern technology and innovation.",
        "author" => "NewsDesk Team",
        "date" => "2026-10-12"
    ],
    [
        "id" => 3,
        "title" => "University Cultural Festival Announced",
        "category" => "Events",
        "description" => "The university is preparing for an exciting cultural festival with student performances.",
        "author" => "NewsDesk Team",
        "date" => "2026-10-18"
    ]
];

// ==========================================
// GET SEARCH AND FILTER VALUES
// ==========================================

$searchTerm = trim($_GET['search'] ?? '');
$selectedCategory = trim($_GET['category'] ?? '');
$selectedDate = trim($_GET['date'] ?? '');

// Available categories
$categories = ['Academic', 'Events', 'Sports', 'General'];

// Validate the selected date
$validDate = false;

if ($selectedDate !== '') {
    $dateObject = DateTime::createFromFormat('Y-m-d', $selectedDate);

    $validDate = $dateObject &&
                 $dateObject->format('Y-m-d') === $selectedDate;
}

// ==========================================
// FILTER NEWS ARTICLES
// ==========================================

$filteredArticles = array_filter(
    $newsArticles,
    function ($article) use (
        $searchTerm,
        $selectedCategory,
        $selectedDate,
        $validDate
    ) {

        // Search in title, description and author
        $matchesSearch = (
            $searchTerm === '' ||
            stripos($article['title'], $searchTerm) !== false ||
            stripos($article['description'], $searchTerm) !== false ||
            stripos($article['author'], $searchTerm) !== false
        );

        // Match selected category
        $matchesCategory = (
            $selectedCategory === '' ||
            $article['category'] === $selectedCategory
        );

        // Match selected publication date
        $matchesDate = (
            $selectedDate === '' ||
            ($validDate && $article['date'] === $selectedDate)
        );

        // All selected filters must match
        return $matchesSearch &&
               $matchesCategory &&
               $matchesDate;
    }
);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Latest News | NSBM NewsDesk</title>

    <!-- Main website stylesheet -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <!-- News page stylesheet -->
    <link rel="stylesheet" href="../assets/css/news.css">
</head>

<body>

    <!-- ======================================
         WEBSITE HEADER
    ======================================= -->

    <header class="site-header">

        <div class="container header-content">

            <a href="home.php" class="logo">
                NSBM <span>NewsDesk</span>
            </a>

            <!-- Main Navigation -->
            <nav class="main-nav">

                <a href="home.php">Home</a>

                <a href="news.php" class="active">News</a>

                <a href="search.php">Search</a>

                <a href="auth/login.php">Login</a>

                <a href="auth/register.php" class="nav-button">
                    Register
                </a>

            </nav>

        </div>

    </header>


    <!-- ======================================
         MAIN NEWS CONTENT
    ======================================= -->

    <main class="news-page">

        <section class="container">

            <!-- Page Heading -->
            <div class="page-heading">

                <p class="section-label">
                    NSBM GREEN UNIVERSITY
                </p>

                <h1>Latest Campus News</h1>

                <p>
                    Stay updated with the latest news, events and
                    announcements from NSBM.
                </p>

            </div>


            <!-- ======================================
                 SEARCH AND FILTER FORM
            ======================================= -->

            <form class="news-filters" method="GET" action="news.php">

                <!-- Search Field -->
                <div class="filter-group">

                    <label for="search">Search News</label>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        placeholder="Search for news..."
                        value="<?= htmlspecialchars($searchTerm) ?>"
                    >

                </div>


                <!-- Category Filter -->
                <div class="filter-group">

                    <label for="category">Category</label>

                    <select id="category" name="category">

                        <option value="">All Categories</option>

                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?= htmlspecialchars($category) ?>"
                                <?= $selectedCategory === $category ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($category) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Publication Date Filter -->
                <div class="filter-group">

                    <label for="date">Published Date</label>

                    <input
                        type="date"
                        id="date"
                        name="date"
                        value="<?= htmlspecialchars($selectedDate) ?>"
                    >

                </div>


                <!-- Search Button -->
                <button type="submit" class="filter-button">
                    Search
                </button>


                <!-- Reset Button -->
                <a href="news.php" class="reset-button">
                    Clear Filters
                </a>

            </form>


            <!-- ======================================
                 NEWS RESULTS
            ======================================= -->

            <div class="news-results">

                <!-- Results Heading -->
                <div class="results-heading">

                    <h2>All News</h2>

                    <span>
                        <?= count($filteredArticles) ?> Articles Found
                    </span>

                </div>


                <!-- News Cards Grid -->
                <div class="news-grid">

                    <?php if (count($filteredArticles) > 0): ?>

                        <?php foreach ($filteredArticles as $article): ?>

                            <article class="news-card">

                                <!-- News Image Placeholder -->
                                <div class="news-image-placeholder">

                                    <span class="placeholder-label">
                                        NSBM NEWSDESK
                                    </span>

                                    <h3>
                                        <?= htmlspecialchars($article['category']) ?>
                                    </h3>

                                    <span class="placeholder-caption">
                                        Campus News & Updates
                                    </span>

                                </div>


                                <!-- News Card Information -->
                                <div class="news-card-content">

                                    <!-- News Category -->
                                    <span class="news-category">
                                        <?= htmlspecialchars($article['category']) ?>
                                    </span>


                                    <!-- News Title -->
                                    <h3>
                                        <?= htmlspecialchars($article['title']) ?>
                                    </h3>


                                    <!-- News Description -->
                                    <p class="news-description">
                                        <?= htmlspecialchars($article['description']) ?>
                                    </p>


                                    <!-- Author and Date -->
                                    <div class="news-meta">

                                        <span>
                                            <?= htmlspecialchars($article['author']) ?>
                                        </span>

                                        <span>
                                            <?= date("d M Y", strtotime($article['date'])) ?>
                                        </span>

                                    </div>


                                    <!-- Read More Link -->
                                    <a
                                        href="article.php?id=<?= (int)$article['id'] ?>"
                                        class="read-more"
                                    >
                                        Read More &rarr;
                                    </a>

                                </div>

                            </article>

                        <?php endforeach; ?>


                    <?php else: ?>

                        <!-- Empty State -->
                        <div class="no-results">

                            <h3>No News Articles Found</h3>

                            <p>
                                Sorry, no articles match your search
                                or selected filters.
                            </p>

                            <a href="news.php" class="filter-button">
                                View All News
                            </a>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </section>

    </main>




</body>
</html>
