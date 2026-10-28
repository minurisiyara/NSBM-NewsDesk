
<?php
// Temporary sample content for the homepage.
// Later, we will load this information from MySQL.

$featuredStories = [
    [
        'category' => 'Academic',
        'title' => 'NSBM Ranked Among Top 10 Private Universities in Sri Lanka',
        'date' => 'September 28, 2025',
        'image' => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=1400&q=80'
    ],
    [
        'category' => 'Sports',
        'title' => 'Green University Celebrates Sporting Success',
        'date' => 'September 24, 2025',
        'image' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=1400&q=80'
    ],
    [
        'category' => 'Events',
        'title' => 'Innovation Takes Centre Stage at University Tech Expo',
        'date' => 'September 23, 2025',
        'image' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1400&q=80'
    ]
];

$latestNews = [
    [
        'category' => 'Academic',
        'title' => 'NSBM Opens a New Chapter in AI Research',
        'summary' => 'Discover new opportunities in research and technology.',
        'date' => 'September 25, 2025',
        'image' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'category' => 'Sports',
        'title' => 'Green University Celebrates Sporting Success',
        'summary' => 'Students celebrate teamwork and sporting achievements.',
        'date' => 'September 24, 2025',
        'image' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'category' => 'Events',
        'title' => 'Innovation at the University Tech Expo',
        'summary' => 'Students present their creative ideas and projects.',
        'date' => 'September 23, 2025',
        'image' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=800&q=80'
    ]
];

$announcements = [
    ['day' => '28', 'month' => 'SEP', 'title' => 'September Convocation', 'detail' => 'Main Auditorium'],
    ['day' => '27', 'month' => 'SEP', 'title' => 'Career Fair 2025', 'detail' => 'Student Centre'],
    ['day' => '28', 'month' => 'SEP', 'title' => 'Inter-faculty Sports Meet', 'detail' => 'Sports Complex'],
    ['day' => '29', 'month' => 'SEP', 'title' => 'Research Symposium', 'detail' => 'Faculty of Computing'],
    ['day' => '30', 'month' => 'SEP', 'title' => 'New Intake Orientation', 'detail' => 'University Premises']
];

// Escape text before displaying it in HTML.
function escapeHtml($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>NSBM NewsDesk | Campus News</title>

    <!-- Connect the homepage to the main CSS file -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <!-- HEADER: website branding and navigation -->
    <header class="site-header">
        <a class="brand" href="home.php">
            <span class="brand-icon">N</span>
            <span>NSBM <strong>NewsDesk</strong></span>
        </a>

        <nav class="main-nav" aria-label="Main navigation">
            <a class="active" href="home.php">Home</a>
            <a href="news.php">News</a>
            <a href="news.php?category=Academic">Academic</a>
            <a href="news.php?category=Sports">Sports</a>
            <a href="news.php?category=Events">Events</a>
            <a href="news.php">Gallery</a>
            <a href="#footer">About</a>
        </nav>

        <form class="header-search" action="search.php" method="get">
            <input
                type="search"
                name="q"
                placeholder="Search news..."
                aria-label="Search news"
            >
            <button type="submit" aria-label="Submit search">⌕</button>
        </form>

        <div class="account-links">
           <a class="login-link" href="../backend/auth/login.php">Login</a>
           <a class="signup-link" href="../backend/auth/register.php">Sign Up</a>
        </div>
    </header>

    <!-- BREAKING NEWS STRIP -->
    <div class="breaking-strip">
        <strong>⚡ BREAKING NEWS</strong>
        <span>NSBM celebrates achievements across the university community.</span>
        <a href="news.php">Explore news →</a>
    </div>

    <main class="page-container">

        <!-- FEATURED STORY AND ANNOUNCEMENTS -->
        <section class="hero-layout">

            <div class="featured-section">
                <p class="eyebrow">THE CAMPUS, CONNECTED</p>

                <div class="hero-slideshow">
                    <?php foreach ($featuredStories as $index => $story): ?>
                        <article
                            class="hero-slide <?= $index === 0 ? 'is-active' : '' ?>"
                            style="background-image: url('<?= escapeHtml($story['image']) ?>')"
                        >
                            <div class="hero-overlay">
                                <span class="category-badge">
                                    <?= escapeHtml($story['category']) ?>
                                </span>

                                <h1><?= escapeHtml($story['title']) ?></h1>

                                <p><?= escapeHtml($story['date']) ?> · Campus News</p>

                                <a class="primary-button" href="article.php">
                                    Read Full Story →
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>

                    <div class="slide-controls">
                        <button type="button" id="previousSlide" aria-label="Previous story">‹</button>
                        <button type="button" id="nextSlide" aria-label="Next story">›</button>
                    </div>
                </div>
            </div>

            <!-- ANNOUNCEMENTS PANEL -->
            <aside class="announcements-panel">
                <div class="section-heading">
                    <h2>Announcements</h2>
                    <a href="#announcements">View All</a>
                </div>

                <?php foreach ($announcements as $announcement): ?>
                    <article class="announcement-item">
                        <div class="announcement-date">
                            <strong><?= escapeHtml($announcement['day']) ?></strong>
                            <span><?= escapeHtml($announcement['month']) ?></span>
                        </div>

                        <div>
                            <h3><?= escapeHtml($announcement['title']) ?></h3>
                            <p><?= escapeHtml($announcement['detail']) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </aside>

        </section>

        <!-- LATEST NEWS CARDS -->
        <section class="latest-section">
            <div class="section-heading">
                <h2>Latest News</h2>
                <a href="news.php">View All News →</a>
            </div>

            <div class="news-grid">
                <?php foreach ($latestNews as $index => $news): ?>
                    <article class="news-card">
                        <a href="article.php?id=<?= $index + 1 ?>">
                            <img
                                src="<?= escapeHtml($news['image']) ?>"
                                alt="<?= escapeHtml($news['title']) ?>"
                                loading="lazy"
                            >
                        </a>

                        <div class="news-card-content">
                            <span class="news-category">
                                <?= escapeHtml($news['category']) ?>
                            </span>

                            <h3>
                                <a href="article.php?id=<?= $index + 1 ?>">
                                    <?= escapeHtml($news['title']) ?>
                                </a>
                            </h3>

                            <p class="news-summary">
                                <?= escapeHtml($news['summary']) ?>
                            </p>

                            <div class="news-card-footer">
                                <span><?= escapeHtml($news['date']) ?></span>
                                <a href="article.php?id=<?= $index + 1 ?>">Read More →</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <!-- FOOTER -->
    <footer class="site-footer" id="footer">
        <div class="footer-about">
            <a class="brand" href="home.php">
                <span class="brand-icon">N</span>
                <span>NSBM <strong>NewsDesk</strong></span>
            </a>

            <p>
                Your window into life at NSBM Green University.
                Stories, achievements and moments from our community.
            </p>

            <p><a href="news.php">Explore the newsroom →</a></p>
        </div>

        <div class="quick-links">
            <h2>Quick Links</h2>
            <a href="https://www.nsbm.ac.lk/">University Website ↗</a>
            <a href="#footer">Student Portal ↗</a>
            <a href="#announcements">Academic Calendar ↗</a>
            <a href="#footer">Digital Library ↗</a>
        </div>

        <p class="copyright">
            © <?= date('Y') ?> NSBM NewsDesk. All rights reserved.
        </p>
    </footer>

    <!-- JavaScript controls the featured-news slideshow -->
    <script src="../assets/js/main.js"></script>

</body>
</html>
