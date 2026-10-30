
<?php
// Temporary sample news data.
// Later, this will be replaced with published articles from the backend.

$newsArticles = [
    [
        "id" => 1,
        "title" => "NSBM Students Celebrate Annual Sports Meet",
        "category" => "Sports",
        "description" => "Students participated in various sporting events during the annual university sports meet.",
        "author" => "NewsDesk Team",
        "date" => "2026-10-15",
        "image" => "../assets/images/sports.jpg"
    ],
    [
        "id" => 2,
        "title" => "New Technology Workshop at NSBM",
        "category" => "Academic",
        "description" => "An informative workshop introduced students to modern technology and innovation.",
        "author" => "NewsDesk Team",
        "date" => "2026-10-12",
        "image" => "../assets/images/technology.jpg"
    ],
    [
        "id" => 3,
        "title" => "University Cultural Festival Announced",
        "category" => "Events",
        "description" => "The university is preparing for an exciting cultural festival with student performances.",
        "author" => "NewsDesk Team",
        "date" => "2026-10-18",
        "image" => "../assets/images/festival.jpg"
    ]

];

// ==========================================
// NEWS SEARCH AND FILTER FUNCTIONALITY
// ==========================================

// Get search and filter values from the URL
$searchTerm = trim($_GET['search'] ?? '');
$selectedCategory = trim($_GET['category'] ?? '');
$selectedDate = trim($_GET['date'] ?? '');

// Validate the selected date
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

        // Match selected publication date
        $matchesDate = (
            $selectedDate === '' ||
            ($validDate && $article['date'] === $selectedDate)
        );

        // All selected conditions must match
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

    <!-- Website Header -->
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
                <a href="auth/register.php" class="nav-button">Register</a>
            </nav>

        </div>
    </header>

    <!-- Main News Content -->
    <main class="news-page">

        <section class="container">

            <!-- Page Heading -->
            <div class="page-heading">
                <p class="section-label">NSBM GREEN UNIVERSITY</p>
                <h1>Latest Campus News</h1>
                <p>Stay updated with the latest news, events and announcements from NSBM.</p>
            </div>

            <!-- Search and Filter Section -->
            <form class="news-filters" method="GET" action="news.php">

                <div class="filter-group">
                    <label for="search">Search News</label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        placeholder="Search for news..."
                        
<select id="category" name="category">

    <option value="">All Categories</option>

    <option value="Academic"
        <?= $selectedCategory === 'Academic' ? 'selected' : '' ?>>
        Academic
    </option>

    <option value="Events"
        <?= $selectedCategory === 'Events' ? 'selected' : '' ?>>
        Events
    </option>

    <option value="Sports"
        <?= $selectedCategory === 'Sports' ? 'selected' : '' ?>>
        Sports
    </option>

    <option value="General"
        <?= $selectedCategory === 'General' ? 'selected' : '' ?>>
        General
    </option>

</select>
                </div>

                <div class="filter-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <option value="Academic">Academic</option>
                        <option value="Events">Events</option>
                        <option value="Sports">Sports</option>
                        <option value="General">General</option>
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

                <button type="submit" class="filter-button">Search</button>

                <a href="news.php" class="reset-button">Clear Filters</a>

            </form>

            <!-- News Articles -->
            <div class="news-results">

                
<div class="results-heading">
    <h2>All News</h2>
    <span><?= count($filteredArticles) ?> Articles Found</span>
</div>

<div class="news-grid">

    <?php if (count($filteredArticles) > 0): ?>

        <?php foreach ($filteredArticles as $article): ?>

            <article class="news-card">

                <!-- Professional placeholder for news image -->
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

                <div class="news-card-content">

                    <span class="news-category">
                        <?= htmlspecialchars($article['category']) ?>
                    </span>

                    <h3>
                        <?= htmlspecialchars($article['title']) ?>
                    </h3>

                    <p class="news-description">
                        <?= htmlspecialchars($article['description']) ?>
                    </p>

                    <div class="news-meta">
                        <span>
                            <?= htmlspecialchars($article['author']) ?>
                        </span>

                        <span>
                            <?= date("d M Y", strtotime($article['date'])) ?>
                        </span>
                    </div>

                    <a
                        href="article.php?id=<?= $article['id'] ?>"
                        class="read-more"
                    >
                        Read More →
                    </a>

                </div>

            </article>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="no-results">
            <h3>No News Articles Found</h3>

            <p>
                Sorry, no articles match your search or selected filters.
            </p>

            <a href="news.php" class="filter-button">
                View All News
            </a>
        </div>

    <?php endif; ?>

</div>
                </div>

            </div>

        </section>

    </main>

    <!-- Website Footer -->
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date("Y") ?> NSBM NewsDesk. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>

.no-results {
    grid-column: 1 / -1;
    background-color: #ffffff;
    padding: 55px 20px;
    text-align: center;
    border-radius: 10px;
    border: 1px solid #e5eae6;
}

.no-results h3 {
    color: #174d35;
    font-size: 22px;
    margin-bottom: 10px;
}

.no-results p {
    color: #758078;
    font-size: 14px;
    margin-bottom: 22px;
}