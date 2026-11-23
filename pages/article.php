
<?php

require_once dirname(__DIR__) . '/backend/config/session.php';

// Generate CSRF token for comments
if (empty($_SESSION['comment_csrf'])) {
    $_SESSION['comment_csrf'] = bin2hex(random_bytes(32));
}

// Temporary sample article data
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

// Get article ID from URL
$articleId = isset($_GET['id']) ? (int) $_GET['id'] : 1;

// Temporary sample data contains only article ID 1
$articleFound = ($articleId === $article['id']);

// Temporary related articles
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

    <link rel="stylesheet" href="../assets/css/article.css">
</head>

<body>

<!-- ================= HEADER / NAVIGATION ================= -->

<header class="site-header">

    <div class="header-container">

        <a href="home.php" class="logo">
            <img src="../assets/images/nsbm-logo.png" alt="NSBM Green University Logo">
            <span>NewsDesk</span>
        </a>

        <nav class="main-nav">
            <a href="home.php">Home</a>
            <a href="news.php">News</a>
            <a href="news.php">Categories</a>
            <a href="search.php">Search</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="student/dashboard.php">Dashboard</a>
            <?php else: ?>
                <a href="auth/login.php" class="login-link">Login</a>
            <?php endif; ?>

        </nav>

    </div>

</header>


<?php if ($articleFound): ?>

<!-- ================= ARTICLE CONTENT ================= -->

<main class="article-page">

    <div class="article-container">

        <article class="article-main">

            <!-- Article heading -->

            <div class="article-header">

                <span class="article-category">
                    <?php echo htmlspecialchars($article['category']); ?>
                </span>

                <h1>
                    <?php echo htmlspecialchars($article['title']); ?>
                </h1>

                <div class="article-meta">

                    <span>
                        By <?php echo htmlspecialchars($article['author']); ?>
                    </span>

                    <span>
                        <?php echo htmlspecialchars($article['date']); ?>
                    </span>

                    <span>
                        <?php echo htmlspecialchars($article['read_time']); ?>
                    </span>

                </div>

            </div>


            <!-- Article image -->

            <div class="article-image">

                <img
                    src="<?php echo htmlspecialchars($article['image']); ?>"
                    alt="<?php echo htmlspecialchars($article['title']); ?>"
                >

            </div>


            <!-- Article actions -->

            <div class="article-actions">

                <button
                    type="button"
                    class="bookmark-btn"
                    id="bookmarkBtn"
                    aria-label="Save article"
                >
                    ♡ <span>Save Article</span>
                </button>

                <button
                    type="button"
                    class="share-btn"
                    id="shareBtn"
                >
                    Share Article
                </button>

            </div>


            <!-- Article body -->

            <div class="article-content">

                <?php foreach ($article['content'] as $paragraph): ?>

                    <p>
                        <?php echo htmlspecialchars($paragraph); ?>
                    </p>

                <?php endforeach; ?>

            </div>


            <!-- ================= COMMENTS ================= -->

            <section class="comments-section">

                <h2>Comments</h2>

                <p class="comments-description">
                    Share your thoughts about this article.
                </p>

                <?php if (
                    isset($_SESSION['user_id']) &&
                    ($_SESSION['user_role'] ?? '') === 'student'
                ): ?>

                    <form
                        id="commentForm"
                        method="POST"
                        action="../backend/comments/create.php"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo htmlspecialchars($_SESSION['comment_csrf']); ?>"
                        >

                        <input
                            type="hidden"
                            name="article_id"
                            value="<?php echo (int) $articleId; ?>"
                        >

                        <label for="commentText">Your Comment</label>

                        <textarea
                            id="commentText"
                            name="content"
                            placeholder="Write your comment..."
                            rows="4"
                            maxlength="2000"
                            required
                        ></textarea>

                        <p>Maximum 2000 characters.</p>

                        <button type="submit" class="submit-comment">
                            Post Comment
                        </button>

                    </form>

                <?php else: ?>

                    <p>
                        Please
                        <a href="auth/login.php">login</a>
                        as a student to post a comment.
                    </p>

                <?php endif; ?>

                <div id="commentMessage" class="comment-message">

                    <?php if (isset($_GET['comment_success'])): ?>
                        Your comment was submitted and is awaiting approval.
                    <?php elseif (isset($_GET['comment_error'])): ?>
                        Unable to submit your comment. Please try again.
                    <?php endif; ?>

                </div>

                <div id="commentList">
                    <p>Comments will appear here.</p>
                </div>

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
<script src="../assets/js/comments.js"></script>

</body>
</html>
