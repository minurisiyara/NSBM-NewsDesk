<?php
// Temporary sample article data for frontend development.
// Later, this data can be loaded from the database.

$article = [
    "id" => 1,
    "title" => "NSBM Green University Organizes Annual Technology Exhibition",
    "category" => "Technology",
    "author" => "NSBM News Desk",
    "date" => "October 30, 2026",
    "read_time" => "5 min read",
    "image" => "../assets/images/technology.jpg",
    "content" => [
        "NSBM Green University recently organized its annual technology exhibition, bringing together students, lecturers and technology enthusiasts to showcase innovative projects and creative ideas.",
        "The exhibition featured a wide range of student projects covering software development, networking, artificial intelligence and emerging technologies. Students had the opportunity to demonstrate their work and explain the ideas behind their projects.",
        "The event also provided a platform for students to improve their communication skills, exchange knowledge and gain valuable feedback from academic staff and visitors.",
        "NSBM continues to encourage innovation, creativity and practical learning through events that help students develop the skills needed for the future."
    ]
];

// Get the article ID from the URL.
$articleId = isset($_GET['id']) ? (int) $_GET['id'] : 1;

// Check whether the requested article exists.
// This is temporary sample data containing only article ID 1.
$articleFound = ($articleId === $article['id']);

// Temporary related articles.
$relatedArticles = [
    [
        "title" => "Students Explore New Technologies",
        "category" => "Technology",
        "date" => "October 25, 2026",
        "link" => "article.php?id=1"
    ],
    [
        "title" => "University News and Events",
        "category" => "Campus News",
        "date" => "October 22, 2026",
        "link" => "article.php?id=1"
    ],
    [
        "title" => "Innovation and Creativity at NSBM",
        "category" => "Education",
        "date" => "October 20, 2026",
        "link" => "article.php?id=1"
    ]
];
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
    <link rel="stylesheet" href="../assets/css/article.css">
</head>

<body>

<!-- ================= HEADER / NAVIGATION ================= -->
<header class="site-header">
    <div class="header-container">

        <a href="home.php" class="logo">
            NSBM <span>NewsDesk</span>
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
<script src="../assets/js/article.js"></script>

</body>
</html>
