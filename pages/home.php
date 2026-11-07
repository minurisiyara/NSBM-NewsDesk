
<?php
// Temporary homepage news data
$featuredNews = [
    "category" => "Academic",
    "title" => "NSBM Ranked Among Top 10 Private Universities in Sri Lanka",
    "date" => "Sep 28, 2025",
    "reading_time" => "3 min read",
    "image" => "https://images.unsplash.com/photo-1562774053-701939374585?w=1200"
];

$latestNews = [
    [
        "category" => "Academic",
        "title" => "NSBM opens a new chapter in AI research",
        "description" => "A new research initiative brings students and industry experts together to shape the future of AI.",
        "date" => "Sep 25, 2025",
        "reading_time" => "4 min read",
        "image" => "https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=800"
    ],
    [
        "category" => "Sports",
        "title" => "Green University cricketers lift the championship trophy",
        "description" => "Team NSBM celebrates a memorable victory after an outstanding inter-university tournament.",
        "date" => "Sep 24, 2025",
        "reading_time" => "3 min read",
        "image" => "https://images.unsplash.com/photo-1531415074968-036ba1b575da?w=800"
    ],
    [
        "category" => "Events",
        "title" => "Innovation takes centre stage at Tech Expo 2025",
        "description" => "Student-led ideas, live demonstrations and bold solutions make this year's showcase one to remember.",
        "date" => "Sep 23, 2025",
        "reading_time" => "5 min read",
        "image" => "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800"
    ]
];

$announcements = [
    ["day" => "26", "month" => "SEP", "title" => "September convocation", "details" => "9:00 AM • Main Auditorium"],
    ["day" => "27", "month" => "SEP", "title" => "Career Fair 2025", "details" => "10:00 AM • Student Centre"],
    ["day" => "28", "month" => "SEP", "title" => "Inter-faculty sports meet", "details" => "8:00 AM • Sports Complex"],
    ["day" => "29", "month" => "SEP", "title" => "Research symposium", "details" => "9:30 AM • Faculty of Computing"],
    ["day" => "30", "month" => "SEP", "title" => "New intake orientation", "details" => "10:00 AM • University premises"]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Home | NSBM NewsDesk</title>

    <link rel="stylesheet" href="../assests/css/home.css">
</head>

<body>

<!-- Navigation Bar -->
<header class="navbar">

    <a href="home.php" class="logo">
    <img src="../assests/images/nsbm-logo.png" alt="NSBM Green University Logo">
    <span>NewsDesk</span>
    </a>

    <nav class="nav-links">
        <a href="home.php" class="active">Home</a>
        <a href="news.php">News</a>
        <a href="news.php?category=Academic">Academic</a>
        <a href="news.php?category=Sports">Sports</a>
        <a href="news.php?category=Events">Events</a>
        <a href="news.php?category=Gallery">Gallery</a>
        <a href="#">About</a>
    </nav>

    <div class="nav-actions">
        <form action="search.php" method="GET" class="search-form">
            <input type="search" name="q" placeholder="Search news, events...">
            <button type="submit">⌕</button>
        </form>

        <a href="auth/login.php" class="login-btn">Login</a>
        <a href="auth/register.php" class="signup-btn">Sign Up</a>
    </div>

</header>

<!-- Breaking News Ticker -->
<section class="breaking-news">
    <strong>✦ BREAKING NEWS</strong>
    <span>NSBM achieves top 10 in Sri Lanka's private university rankings...</span>
</section>

<main class="main-container">

    <!-- Date and Welcome Text -->
    <div class="welcome-row">
        <p>THE CAMPUS, CONNECTED.</p>
        <span>Friday, September 28, 2025</span>
    </div>

    <!-- Featured News and Announcements -->
    <section class="featured-layout">

        <!-- Featured Article -->
        <div class="featured-news"
             style="background-image: url('<?= htmlspecialchars($featuredNews['image']) ?>');">

            <div class="featured-content">

                <span class="category-label">
                    <?= htmlspecialchars($featuredNews['category']) ?>
                </span>

                <h1><?= htmlspecialchars($featuredNews['title']) ?></h1>

                <p>
                    <?= htmlspecialchars($featuredNews['date']) ?>
                    •
                    <?= htmlspecialchars($featuredNews['reading_time']) ?>
                </p>

                <a href="news.php" class="read-story-btn">
                    Read Full Story →
                </a>

            </div>
        </div>

        <!-- Announcements -->
        <aside class="announcements">

            <div class="section-heading">
                <h2>Announcements</h2>
                <a href="news.php">View All</a>
            </div>

            <?php foreach ($announcements as $announcement): ?>

                <div class="announcement-item">

                    <div class="date-box">
                        <strong><?= htmlspecialchars($announcement['day']) ?></strong>
                        <span><?= htmlspecialchars($announcement['month']) ?></span>
                    </div>

                    <div class="announcement-info">
                        <h3><?= htmlspecialchars($announcement['title']) ?></h3>
                        <p><?= htmlspecialchars($announcement['details']) ?></p>
                    </div>

                </div>

            <?php endforeach; ?>

        </aside>

    </section>

    <!-- Latest News -->
    <section class="latest-section">

        <div class="section-heading latest-heading">
            <h2>Latest News</h2>
            <a href="news.php">View All News →</a>
        </div>

        <div class="latest-grid">

            <?php foreach ($latestNews as $article): ?>

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
                            <span><?= htmlspecialchars($article['reading_time']) ?></span>
                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    </section>

</main>

<!-- Footer -->
<footer class="home-footer">

    <div class="footer-about">
        <h2>🍃 NSBM NewsDesk</h2>

        <p>
            Your window into life at NSBM Green University.
            Stories, achievements and moments from our community.
        </p>

        <div class="footer-links">
            <a href="#">Contact the newsroom ↗</a>
            <a href="#">Submit a story ↗</a>
        </div>

        <small>© 2025 NSBM Green University. All rights reserved.</small>
    </div>

    <div class="quick-links">
        <h3>🔗 Quick Links</h3>

        <a href="#">🌐 University website ↗</a>
        <a href="#">🎓 Student portal ↗</a>
        <a href="#">📅 Academic calendar ↗</a>
        <a href="#">📖 Digital library ↗</a>
    </div>

</footer>

</body>
</html>
