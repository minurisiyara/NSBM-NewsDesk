<?php
// Temporary sample article data for frontend development.

$articles = [

    // Article 1 - Academic
    1 => [
        "id" => 1,
        "title" => "NSBM opens a new chapter in AI research",
        "category" => "Academic",
        "author" => "NSBM News Desk",
        "date" => "September 25, 2025",
        "read_time" => "4 min read",
        "image" => "https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=800",
        "content" => [
            "A new research initiative brings students and industry experts together to explore artificial intelligence.",
            "The initiative provides students with opportunities to learn about artificial intelligence and understand its practical applications.",
            "Through collaboration and research, students can develop new ideas and improve their knowledge of emerging technologies."
        ]
    ],

    // Article 2 - Sports
    2 => [
        "id" => 2,
        "title" => "Green University cricketers lift the championship trophy",
        "category" => "Sports",
        "author" => "NSBM News Desk",
        "date" => "September 24, 2025",
        "read_time" => "3 min read",
        "image" => "https://images.unsplash.com/photo-1531415074968-036ba1b575da?w=800",
        "content" => [
            "Team NSBM celebrates a memorable victory after an outstanding inter-university tournament.",
            "The cricket team demonstrated excellent teamwork, determination and sportsmanship throughout the competition.",
            "This achievement highlights the importance of sports and teamwork in university life."
        ]
    ],

    // Article 3 - Events
    3 => [
        "id" => 3,
        "title" => "Innovation takes centre stage at Tech Expo 2025",
        "category" => "Events",
        "author" => "NSBM News Desk",
        "date" => "September 23, 2025",
        "read_time" => "4 min read",
        "image" => "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800",
        "content" => [
            "Students showcase creative ideas and innovative technology projects at Tech Expo 2025.",
            "The exhibition provides a platform for students to present their projects and demonstrate their technical knowledge.",
            "Events like this encourage creativity, innovation and practical learning among university students."
        ]
    ],

    // Article 4 - Academic
    4 => [
        "id" => 4,
        "title" => "NSBM celebrates academic excellence",
        "category" => "Academic",
        "author" => "NSBM News Desk",
        "date" => "September 20, 2025",
        "read_time" => "3 min read",
        "image" => "https://images.unsplash.com/photo-1562774053-701939374585?w=800",
        "content" => [
            "Students and lecturers celebrate achievements and academic success at NSBM.",
            "Academic achievements reflect the dedication and hard work of students and the guidance provided by lecturers.",
            "NSBM continues to encourage students to develop their knowledge and achieve their educational goals."
        ]
    ],

    // Article 5 - Sports
    5 => [
        "id" => 5,
        "title" => "Inter-faculty sports meet begins",
        "category" => "Sports",
        "author" => "NSBM News Desk",
        "date" => "September 18, 2025",
        "read_time" => "3 min read",
        "image" => "https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=800",
        "content" => [
            "Students come together to represent their faculties in a range of sporting events.",
            "The sports meet gives students an opportunity to demonstrate their abilities, build friendships and represent their faculties.",
            "The event promotes teamwork, healthy competition and an active university community."
        ]
    ],

    // Article 6 - Events
    6 => [
        "id" => 6,
        "title" => "New student orientation programme",
        "category" => "Events",
        "author" => "NSBM News Desk",
        "date" => "September 15, 2025",
        "read_time" => "3 min read",
        "image" => "https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=800",
        "content" => [
            "New students are welcomed to the NSBM Green University community.",
            "The orientation programme introduces new students to university facilities, academic activities and student life.",
            "It also provides an opportunity for students to meet lecturers, staff members and fellow students."
        ]
    ]

];

// Get the article ID from the URL.
$articleId = isset($_GET['id']) ? (int) $_GET['id'] : 1;

// Check whether the requested article exists.
$articleFound = isset($articles[$articleId]);

// Load the selected article.
if ($articleFound) {
    $article = $articles[$articleId];
} else {
    $article = [];
}

// Generate related articles, excluding the current article.
$relatedArticles = [];

foreach ($articles as $item) {
    if ($item['id'] !== $articleId) {
        $relatedArticles[] = [
            "title" => $item['title'],
            "category" => $item['category'],
            "date" => $item['date'],
            "link" => "article.php?id=" . $item['id']
        ];
    }
}

// Display only the first three related articles.
$relatedArticles = array_slice($relatedArticles, 0, 3);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo $articleFound ? htmlspecialchars($article['title']) : "Article Not Found"; ?>
        | NSBM NewsDesk
    </title>

    <!-- Article page stylesheet -->
    <link rel="stylesheet" href="../assests/css/article.css">
</head>

<body>

<!-- ================= HEADER / NAVIGATION ================= -->
<header class="site-header">
    <div class="header-container">

        <a href="home.php" class="logo">
            <img src="../assests/images/nsbm-logo.png" alt="NSBM Green University Logo"><span>NewsDesk</span>
        </a>

        <nav class="main-nav">
            <a href="home.php">Home</a>
            <a href="news.php">News</a>
            <a href="news.php">Categories</a>
            <a href="search.php">Search</a>
            <a href="login.php" class="login-link">Login</a>
        </nav>

    </div>
</header>


<?php if ($articleFound): ?>

<!-- ================= MAIN ARTICLE ================= -->
<main class="article-page">

    <!-- Breadcrumb navigation -->
    <div class="breadcrumb">
        <a href="home.php">Home</a>
        <span>/</span>
        <a href="news.php">News</a>
        <span>/</span>
        <span>Article Details</span>
    </div>

    <div class="article-layout">

        <!-- Article content -->
        <article class="article-content">

            <span class="article-category">
                <?php echo htmlspecialchars($article['category']); ?>
            </span>

            <h1 class="article-title">
                <?php echo htmlspecialchars($article['title']); ?>
            </h1>

            <div class="article-meta">
                <span>By <?php echo htmlspecialchars($article['author']); ?></span>
                <span>•</span>
                <span><?php echo htmlspecialchars($article['date']); ?></span>
                <span>•</span>
                <span><?php echo htmlspecialchars($article['read_time']); ?></span>
            </div>

            <!-- Main article image -->
            <img
                src="<?php echo htmlspecialchars($article['image']); ?>"
                alt="Technology exhibition at NSBM"
                class="article-main-image"
            >

            <!-- Bookmark button -->
            <div class="article-actions">
                <button type="button" class="bookmark-btn" id="bookmarkBtn">
                    ♡ <span>Save Article</span>
                </button>
            </div>

            <!-- Full article paragraphs -->
            <div class="article-body">

                <?php foreach ($article['content'] as $paragraph): ?>
                    <p><?php echo htmlspecialchars($paragraph); ?></p>
                <?php endforeach; ?>

            </div>

            <!-- Share section -->
            <div class="share-section">
                <h3>Share this article</h3>

                <button type="button" id="shareBtn">
                    Share Article
                </button>
            </div>

            <!-- ================= COMMENTS ================= -->
            <section class="comments-section">

                <h2>Comments</h2>

                <p class="comments-description">
                    Share your thoughts about this article.
                </p>

                <!-- Frontend placeholder form.
                     Comments are not saved to a database yet. -->
                <form id="commentForm">

                    <label for="commentName">Your Name</label>
                    <input
                        type="text"
                        id="commentName"
                        placeholder="Enter your name"
                        required
                    >

                    <label for="commentText">Your Comment</label>
                    <textarea
                        id="commentText"
                        placeholder="Write your comment..."
                        rows="4"
                        required
                    ></textarea>

                    <button type="submit" class="submit-comment">
                        Post Comment
                    </button>

                </form>

                <p id="commentMessage" class="comment-message"></p>

                <div id="commentList"></div>

            </section>

        </article>


        <!-- ================= RELATED ARTICLES ================= -->
        <aside class="related-section">

            <h2>Related Articles</h2>

            <?php foreach ($relatedArticles as $related): ?>

                <div class="related-card">

                    <span class="related-category">
                        <?php echo htmlspecialchars($related['category']); ?>
                    </span>

                    <h3>
                        <?php echo htmlspecialchars($related['title']); ?>
                    </h3>

                    <p>
                        <?php echo htmlspecialchars($related['date']); ?>
                    </p>

                    <a href="<?php echo htmlspecialchars($related['link']); ?>">
                        Read More →
                    </a>

                </div>

            <?php endforeach; ?>

        </aside>

    </div>

</main>

<?php else: ?>

<!-- ================= ARTICLE NOT FOUND ================= -->
<main class="not-found">

    <div class="not-found-icon">!</div>

    <h1>Article Not Found</h1>

    <p>
        Sorry, the article you are looking for is unavailable
        or may have been removed.
    </p>

    <a href="news.php" class="back-to-news">
        Back to News
    </a>

</main>

<?php endif; ?>


<!-- ================= FOOTER ================= -->
<footer class="site-footer">

    <div class="footer-content">
        <h2>NSBM <span>NewsDesk</span></h2>

        <p>
            Your source for the latest news, events and updates
            from NSBM Green University.
        </p>

        <p class="copyright">
            &copy; 2026 NSBM NewsDesk. All rights reserved.
        </p>
    </div>

</footer>


<!-- Article page JavaScript -->
<script src="../assests/js/article.js"></script>

</body>
</html>




