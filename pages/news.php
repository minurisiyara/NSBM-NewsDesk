
<?php
// Temporary sample news data
$newsArticles = [
    [   "id" => 1,
        "category" => "Academic",
        "title" => "NSBM opens a new chapter in AI research",
        "description" => "A new research initiative brings students and industry experts together to explore artificial intelligence.",
        "date" => "September 25, 2025",
        "image" => "https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=800"
    ],
    [   "id" => 2,
        "category" => "Sports",
        "title" => "Green University cricketers lift the championship trophy",
        "description" => "Team NSBM celebrates a memorable victory after an outstanding inter-university tournament.",
        "date" => "September 24, 2025",
        "image" => "https://images.unsplash.com/photo-1531415074968-036ba1b575da?w=800"
    ],
    [   "id" => 3,
        "category" => "Events",
        "title" => "Innovation takes centre stage at Tech Expo 2025",
        "description" => "Students showcase creative ideas and innovative technology projects.",
        "date" => "September 23, 2025",
        "image" => "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800"
    ],
    [   "id" => 4,
        "category" => "Academic",
        "title" => "NSBM celebrates academic excellence",
        "description" => "Students and lecturers celebrate achievements and academic success.",
        "date" => "September 20, 2025",
        "image" => "https://images.unsplash.com/photo-1562774053-701939374585?w=800"
    ],
    [   "id" => 5,
        "category" => "Sports",
        "title" => "Inter-faculty sports meet begins",
        "description" => "Students come together to represent their faculties in a range of sporting events.",
        "date" => "September 18, 2025",
        "image" => "https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=800"
    ],
    [   "id" => 6,
        "category" => "Events",
        "title" => "New student orientation programme",
        "description" => "New students are welcomed to the NSBM Green University community.",
        "date" => "September 15, 2025",
        "image" => "https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=800"
    ]
];

// Read selected category from URL
$selectedCategory = $_GET['category'] ?? 'All';

// Filter news articles
$filteredNews = [];

foreach ($newsArticles as $article) {
    if (
        $selectedCategory === 'All' ||
        $article['category'] === $selectedCategory
    ) {
        $filteredNews[] = $article;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>News | NSBM NewsDesk</title>

    <link rel="stylesheet" href="../assests/css/news.css">
</head>

<body>

<!-- Navigation Bar -->
<header class="navbar">

    <a href="home.php" class="logo">
    <img src="../assests/images/nsbm-logo.png" alt="NSBM Green University Logo">
    <span>NewsDesk</span>
    </a>

    <nav class="nav-links">
        <a href="home.php">Home</a>
        <a href="news.php" class="active">News</a>
        <a href="news.php?category=Academic">Academic</a>
        <a href="news.php?category=Sports">Sports</a>
        <a href="news.php?category=Events">Events</a>
        <a href="news.php?category=Gallery">Gallery</a>
        <a href="#">About</a>
    </nav>

    <div class="nav-actions">
        <form action="search.php" method="GET" class="search-form">
            <input type="search" name="q" placeholder="Search news, events...">
            <button type="submit">Γîò</button>
        </form>

        <a href="auth/login.php" class="login-btn">Login</a>
        <a href="auth/register.php" class="signup-btn">Sign Up</a>
    </div>

</header>

<!-- Breaking News -->
<section class="breaking-news">
    <strong>Γ£ª BREAKING NEWS</strong>
    <span>Stay updated with the latest news from NSBM Green University.</span>
</section>

<!-- News Content -->
<main class="news-container">

    <div class="news-intro">
        <p>THE CAMPUS, CONNECTED.</p>
        <h1>Latest News & Stories</h1>
        <span>Discover the latest updates, achievements and events from our university community.</span>
    </div>

    <!-- Category Filters -->
    <div class="category-filters">

        <a href="news.php"
           class="<?= $selectedCategory === 'All' ? 'selected' : '' ?>">
            All News
        </a>

        <a href="news.php?category=Academic"
           class="<?= $selectedCategory === 'Academic' ? 'selected' : '' ?>">
            Academic
        </a>

        <a href="news.php?category=Sports"
           class="<?= $selectedCategory === 'Sports' ? 'selected' : '' ?>">
            Sports
        </a>

        <a href="news.php?category=Events"
           class="<?= $selectedCategory === 'Events' ? 'selected' : '' ?>">
            Events
        </a>

    </div>

    <h2 class="results-heading">
        <?= htmlspecialchars($selectedCategory) ?> News
        <span>(<?= count($filteredNews) ?> articles)</span>
    </h2>

    <!-- News Cards -->
    <div class="news-grid">

        <?php foreach ($filteredNews as $article): ?>

            <article class="news-card">

                <img
                    src="<?= htmlspecialchars($article['image']) ?>"
                    alt="<?= htmlspecialchars($article['title']) ?>"
                >

                <div class="news-card-content">

                    <span class="news-category">
                        <?= htmlspecialchars($article['category']) ?>
                    </span>

                    <h3><?= htmlspecialchars($article['title']) ?></h3>

                    <p><?= htmlspecialchars($article['description']) ?></p>

                    <div class="news-card-footer">
    <span><?= htmlspecialchars($article['date']) ?></span>

    <a href="article.php?id=<?= (int) $article['id'] ?>">
        Read More
    </a>
    </div>
                

            </article>

        <?php endforeach; ?>

    </div>

    <?php if (empty($filteredNews)): ?>

        <div class="no-news">
            <h2>No news articles found.</h2>
            <p>Please select another category.</p>
            <a href="news.php">View All News</a>
        </div>

    <?php endif; ?>

</main>

<!-- Footer -->
<footer class="home-footer">

    <div class="footer-about">
        <h2>≡ƒìâ NSBM NewsDesk</h2>

        <p>
            Your window into life at NSBM Green University.
            Stories, achievements and moments from our community.
        </p>

        <small>┬⌐ 2025 NSBM Green University. All rights reserved.</small>
    </div>

    <div class="quick-links">
        <h3>Quick Links</h3>
        <a href="home.php">Home</a>
        <a href="news.php">Latest News</a>
        <a href="search.php">Search News</a>
    </div>

</footer>

</body>
</html>
