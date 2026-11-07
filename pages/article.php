
<?php
// --------------------------------------------------
// NSBM NewsDesk - Individual Article Page
// Uses demonstration content only.
// No database connection is required.
// --------------------------------------------------

$articles = [
    1 => [
        "title" => "NSBM Students Participate in Technology Exhibition",
        "category" => "Academic",
        "author" => "NewsDesk Editorial Team",
        "date" => "2026-10-20",
        "image" => "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=1200",
        "description" => "Students explore technology, innovation, and new learning opportunities.",
        "content" => [
            "Technology continues to create new opportunities for students to develop their knowledge and practical skills. Exhibitions can help students explore emerging ideas and understand how technology is used to solve real-world problems.",
            "Students can use activities like these to share ideas, collaborate with others, and learn about different areas of computing and innovation.",
            "This demonstration article shows how a full news story may be presented on NSBM NewsDesk. Replace this sample text with verified, approved university news when the real content is available."
        ]
    ],
    2 => [
        "title" => "University Sports Meet Celebrates Student Talent",
        "category" => "Sports",
        "author" => "NewsDesk Editorial Team",
        "date" => "2026-10-18",
        "image" => "https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=1200",
        "description" => "Students come together to celebrate teamwork and sporting achievements.",
        "content" => [
            "University sports activities provide students with opportunities to develop teamwork, discipline, and sportsmanship.",
            "Taking part in sports can also help students build friendships and balance academic life with physical activity.",
            "This is demonstration content, not a verified report of an actual NSBM sports event."
        ]
    ],
    3 => [
        "title" => "Campus Career Guidance Session Announced",
        "category" => "Events",
        "author" => "NewsDesk Editorial Team",
        "date" => "2026-10-15",
        "image" => "https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=1200",
        "description" => "A sample announcement about career guidance and professional development.",
        "content" => [
            "Career guidance activities can help university students explore potential career paths and understand the skills required in the workplace.",
            "Students can benefit from learning about professional communication, interview preparation, and career planning.",
            "This sample story is included to demonstrate the article page layout. It does not confirm an actual university event."
        ]
    ],
    4 => [
        "title" => "Computing Students Showcase Their Projects",
        "category" => "Technology",
        "author" => "NewsDesk Editorial Team",
        "date" => "2026-10-12",
        "image" => "https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200",
        "description" => "Explore examples of teamwork, programming, and computing projects.",
        "content" => [
            "Computing projects allow students to apply concepts learned in class to practical problems.",
            "Working on a project can strengthen programming, problem-solving, communication, and teamwork skills.",
            "This is a demonstration article. Replace it with verified information before publishing real university news."
        ]
    ],
    5 => [
        "title" => "Student Community Takes Part in Campus Activities",
        "category" => "Events",
        "author" => "NewsDesk Editorial Team",
        "date" => "2026-10-10",
        "image" => "https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=1200",
        "description" => "A sample story highlighting student participation and campus life.",
        "content" => [
            "Campus activities give students opportunities to meet people, exchange ideas, and participate in university life.",
            "These activities can support personal development and help create a welcoming student community.",
            "This content is for frontend testing only and does not describe a verified university activity."
        ]
    ],
    6 => [
        "title" => "New Learning Opportunities for University Students",
        "category" => "Academic",
        "author" => "NewsDesk Editorial Team",
        "date" => "2026-10-08",
        "image" => "https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1200",
        "description" => "A demonstration story about learning, education, and student development.",
        "content" => [
            "Learning opportunities help students strengthen their understanding and develop skills for their future careers.",
            "Students can make the most of their studies by exploring new subjects, working with classmates, and practising what they learn.",
            "This is sample content for the NSBM NewsDesk student project, not an official university announcement."
        ]
    ]
];

// Get the article ID from the URL.
// Example: article.php?id=1
$articleId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

// Select the requested article if it exists.
$article = ($articleId !== false && $articleId !== null)
    ? ($articles[$articleId] ?? null)
    : null;

// Escape text before displaying it in HTML.
function articleEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Find other articles to display as related stories.
$relatedArticles = [];

if ($article !== null) {
    foreach ($articles as $id => $item) {
        if ($id !== $articleId && $item["category"] === $article["category"]) {
            $relatedArticles[$id] = $item;
        }
    }

    // If there are no same-category stories, show other articles instead.
    if (count($relatedArticles) === 0) {
        foreach ($articles as $id => $item) {
            if ($id !== $articleId) {
                $relatedArticles[$id] = $item;
            }
        }
    }

    $relatedArticles = array_slice($relatedArticles, 0, 3, true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= $article !== null
            ? articleEscape($article["title"]) . " | NSBM NewsDesk"
            : "Article Not Found | NSBM NewsDesk" ?>
    </title>

    <!-- Reuse the existing project stylesheet. -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        /* Individual article page styles */
        .article-page {
            max-width: 1000px;
            margin: 0 auto;
            padding: 35px 20px 60px;
        }

        .article-back-link {
            display: inline-block;
            margin-bottom: 25px;
            color: #185c42;
            text-decoration: none;
            font-weight: bold;
        }

        .article-back-link:hover {
            text-decoration: underline;
        }

        .article-demo-notice {
            padding: 12px 16px;
            margin-bottom: 24px;
            border-left: 4px solid #b88724;
            border-radius: 4px;
            background: #fff8e8;
            color: #5c481b;
        }

        .article-category {
            display: inline-block;
            margin-bottom: 12px;
            color: #185c42;
            font-weight: bold;
        }

        .article-title {
            margin: 0 0 16px;
            color: #20372c;
            font-size: clamp(1.8rem, 4vw, 2.7rem);
            line-height: 1.3;
        }

        .article-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 22px;
            margin-bottom: 25px;
            color: #666;
            font-size: 0.95rem;
        }

        .article-feature-image {
            display: block;
            width: 100%;
            max-height: 500px;
            margin-bottom: 28px;
            border-radius: 12px;
            object-fit: cover;
        }

        .article-description {
            color: #46554b;
            font-size: 1.15rem;
            font-weight: 500;
            line-height: 1.8;
        }

        .article-body {
            color: #303b34;
            font-size: 1.05rem;
            line-height: 1.9;
        }

        .article-body p {
            margin-bottom: 22px;
        }

        .related-articles {
            margin-top: 50px;
            padding-top: 28px;
            border-top: 1px solid #e1e7e2;
        }

        .related-articles h2 {
            margin-bottom: 20px;
            color: #185c42;
        }

        .related-article-list {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .related-article-card {
            padding: 18px;
            border: 1px solid #e1e7e2;
            border-radius: 8px;
        }

        .related-article-card h3 {
            margin: 8px 0;
            font-size: 1rem;
            line-height: 1.5;
        }

        .related-article-card a {
            color: #185c42;
            font-weight: bold;
            text-decoration: none;
        }

        .related-article-card a:hover {
            text-decoration: underline;
        }

        .article-not-found {
            padding: 35px 20px;
            border-radius: 10px;
            background: #f5f7f5;
            text-align: center;
        }

        .article-not-found h1 {
            color: #185c42;
        }

        @media (max-width: 650px) {
            .article-page {
                padding: 25px 15px 40px;
            }

            .related-article-list {
                grid-template-columns: 1fr;
            }

            .article-feature-image {
                max-height: 300px;
            }
        }
    </style>
</head>

<body>

<!-- Public navigation header -->
<header class="site-header">
    <div class="header-container">

        <a class="brand" href="home.php">
            <span>NSBM NewsDesk</span>
        </a>

        <nav class="main-nav" aria-label="Main navigation">
            <a href="home.php">Home</a>
            <a class="active" href="news.php">News</a>
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

<main class="article-page">

    <a class="article-back-link" href="news.php">
        ← Back to News
    </a>

    <?php if ($article !== null): ?>

        <div class="article-demo-notice">
            <strong>Development demo:</strong>
            This is sample article content, not a verified official NSBM news report.
        </div>

        <article>

            <span class="article-category">
                <?= articleEscape($article["category"]) ?>
            </span>

            <h1 class="article-title">
                <?= articleEscape($article["title"]) ?>
            </h1>

            <div class="article-meta">
                <span>
                    By <?= articleEscape($article["author"]) ?>
                </span>

                <span>
                    Published:
                    <time datetime="<?= articleEscape($article["date"]) ?>">
                        <?= date("d M Y", strtotime($article["date"])) ?>
                    </time>
                </span>
            </div>

            <img
                class="article-feature-image"
                src="<?= articleEscape($article["image"]) ?>"
                alt="Illustrative image for <?= articleEscape($article["title"]) ?>"
            >

            <p class="article-description">
                <?= articleEscape($article["description"]) ?>
            </p>

            <div class="article-body">
                <?php foreach ($article["content"] as $paragraph): ?>
                    <p><?= articleEscape($paragraph) ?></p>
                <?php endforeach; ?>
            </div>

            <!-- Bookmark placeholder: database integration is pending -->
            <section class="article-bookmark" aria-label="Bookmark article">
                <button type="button" disabled>
                    ☆ Bookmark Article
                </button>
                <p>Bookmark functionality will be available after backend integration.</p>
            </section>

            <!-- Comments placeholder: database integration is pending -->
            <section class="article-comments" aria-labelledby="comments-heading">
                <h2 id="comments-heading">Comments</h2>
                <p>Comments will be available after backend integration.</p>

                <form>
                    <label for="comment-text">Write a comment</label>
                    <textarea
                        id="comment-text"
                        name="comment"
                        rows="4"
                        placeholder="Comments are not available yet."
                        disabled
                    ></textarea>

                    <button type="button" disabled>Submit Comment</button>
                </form>
            </section>

        </article>

        <!-- Related stories from the demo collection -->
        <?php if (count($relatedArticles) > 0): ?>
            <section class="related-articles" aria-labelledby="related-heading">

                <h2 id="related-heading">Related Articles</h2>

                <div class="related-article-list">

                    <?php foreach ($relatedArticles as $id => $related): ?>
                        <article class="related-article-card">

                            <span class="article-category">
                                <?= articleEscape($related["category"]) ?>
                            </span>

                            <h3>
                                <?= articleEscape($related["title"]) ?>
                            </h3>

                            <p>
                                <?= articleEscape($related["description"]) ?>
                            </p>

                            <a href="article.php?id=<?= (int)$id ?>">
                                Read Article →
                            </a>

                        </article>
                    <?php endforeach; ?>

                </div>
            </section>
        <?php endif; ?>

    <?php else: ?>

        <!-- Displayed if the ID is missing or does not exist. -->
        <section class="article-not-found">
            <h1>Article Not Found</h1>

            <p>
                We couldn't find that article. It may not exist or the link may be incorrect.
            </p>

            <a class="article-back-link" href="news.php">
                Browse All News
            </a>
        </section>

    <?php endif; ?>

</main>

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
