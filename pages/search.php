<?php
// Temporary demo news data.
// This will be connected to the database later.

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
        "description" => "A technology workshop was organized to introduce students to modern technology trends.",
        "author" => "NewsDesk Team",
        "date" => "2026-10-12"
    ],
    [
        "id" => 3,
        "title" => "University Cultural Festival Announced",
        "category" => "Events",
        "description" => "NSBM announced its upcoming cultural festival with activities and performances.",
        "author" => "NewsDesk Team",
        "date" => "2026-10-18"
    ]
];

// Get filter values from the URL
$searchTerm = trim($_GET['search'] ?? '');
$selectedCategory = trim($_GET['category'] ?? '');
$selectedDate = trim($_GET['date'] ?? '');
$sortOrder = $_GET['sort'] ?? 'newest';

// Validate the date
$validDate = false;

if ($selectedDate !== '') {
    $dateObject = DateTime::createFromFormat('Y-m-d', $selectedDate);

    $validDate = $dateObject &&
        $dateObject->format('Y-m-d') === $selectedDate;
}

// Filter the news articles
$filteredArticles = array_filter(
    $newsArticles,
    function ($article) use (
        $searchTerm,
        $selectedCategory,
        $selectedDate,
        $validDate
    ) {
        // Search in title and description
        $matchesSearch = (
            $searchTerm === '' ||
            stripos($article['title'], $searchTerm) !== false ||
            stripos($article['description'], $searchTerm) !== false
        );

        // Match selected category
        $matchesCategory = (
            $selectedCategory === '' ||
            $article['category'] === $selectedCategory
        );

        // Match selected date
        $matchesDate = (
            $selectedDate === '' ||
            ($validDate && $article['date'] === $selectedDate)
        );

        return $matchesSearch && $matchesCategory && $matchesDate;
    }
);

// Sort the filtered articles
if ($sortOrder === 'oldest') {
    usort($filteredArticles, function ($a, $b) {
        return strcmp($a['date'], $b['date']);
    });
} else {
    $sortOrder = 'newest';

    usort($filteredArticles, function ($a, $b) {
        return strcmp($b['date'], $a['date']);
    });
}

// Count the search results
$resultCount = count($filteredArticles);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Search News | NSBM NewsDesk</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/news.css">
</head>

<body>

    <!-- Website Header -->
    <header class="site-header">
        <div class="header-container">

            <a href="home.php" class="logo">NSBM NewsDesk</a>

            <nav class="main-nav">
                <a href="home.php">Home</a>
                <a href="news.php">News</a>
                <a href="search.php" class="active">Search</a>
            </nav>

        </div>
    </header>

    <main class="search-page">

        <!-- Search Page Heading -->
        <section class="search-heading">
            <h1>Search News</h1>
            <p>Find the latest news and updates from NSBM Green University.</p>
        </section>

        <!-- Search and Filter Form -->
        <section class="search-filter-section">

            <form action="search.php" method="GET" class="search-filter-form">

                <div class="filter-group">
                    <label for="search">Search News</label>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        placeholder="Enter a news title or keyword"
                        value="<?= htmlspecialchars($searchTerm) ?>"
                    >
                </div>

                <div class="filter-group">
                    <label for="category">Category</label>

                    <select id="category" name="category">
                        <option value="">All Categories</option>

                        <option value="Academic"
                            <?= $selectedCategory === 'Academic' ? 'selected' : '' ?>>
                            Academic
                        </option>

                        <option value="Sports"
                            <?= $selectedCategory === 'Sports' ? 'selected' : '' ?>>
                            Sports
                        </option>

                        <option value="Events"
                            <?= $selectedCategory === 'Events' ? 'selected' : '' ?>>
                            Events
                        </option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="date">Published Date</label>

                    <input
                        type="date"
                        id="date"
                        name="date"
                        value="<?= htmlspecialchars($selectedDate) ?>"
                    >
                </div>

                <div class="filter-group">
                    <label for="sort">Sort By</label>

                    <select id="sort" name="sort">
                        <option value="newest"
                            <?= $sortOrder === 'newest' ? 'selected' : '' ?>>
                            Newest First
                        </option>

                        <option value="oldest"
                            <?= $sortOrder === 'oldest' ? 'selected' : '' ?>>
                            Oldest First
                        </option>
                    </select>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="search-button">
                        Search
                    </button>

                    <a href="search.php" class="clear-button">
                        Clear Filters
                    </a>
                </div>

            </form>

        </section>

        <!-- Search Results -->
        <section class="search-results-section">

            <div class="results-heading">
                <h2>Search Results</h2>

                <p>
                    <?= $resultCount ?>
                    <?= $resultCount === 1 ? 'article' : 'articles' ?> found
                </p>
            </div>

            <?php if ($resultCount > 0): ?>

                <div class="search-results-grid">

                    <?php foreach ($filteredArticles as $article): ?>

                        <article class="search-result-card">

                            <span class="result-category">
                                <?= htmlspecialchars($article['category']) ?>
                            </span>

                            <h3>
                                <?= htmlspecialchars($article['title']) ?>
                            </h3>

                            <p class="result-description">
                                <?= htmlspecialchars($article['description']) ?>
                            </p>

                            <div class="result-meta">
                                <span>
                                    By <?= htmlspecialchars($article['author']) ?>
                                </span>

                                <span>
                                    <?= date('d F Y', strtotime($article['date'])) ?>
                                </span>
                            </div>

                            <a
                                href="article.php?id=<?= $article['id'] ?>"
                                class="read-more"
                            >
                                Read More →
                            </a>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <!-- Display when no articles match -->
                <div class="search-no-results">
                    <h3>No News Articles Found</h3>

                    <p>
                        Sorry, we couldn't find any articles matching your search.
                        Try another keyword or change your filters.
                    </p>

                    <a href="search.php" class="clear-button">
                        Clear Search
                    </a>
                </div>

            <?php endif; ?>

        </section>

    </main>

    <!-- Website Footer -->
    <footer class="site-footer">
        <p>&copy; 2026 NSBM NewsDesk. All Rights Reserved.</p>
    </footer>

</body>
</html>
