<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/backend/config/session.php';

// Check user login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Check student role
if (($_SESSION['user_role'] ?? '') !== 'student') {
    http_response_code(403);
    exit('Access denied.');
}

$name = $_SESSION['user_name'] ?? 'Student';

// Demo bookmarks
$bookmarks = [
    [
        'title' => 'Latest Technology Trends in 2026',
        'category' => 'Technology',
        'date' => 'November 05, 2026',
        'description' => 'Discover the latest technology developments and innovations.'
    ],
    [
        'title' => 'University Events and Activities',
        'category' => 'Education',
        'date' => 'November 04, 2026',
        'description' => 'Explore the latest university events and student activities.'
    ],
    [
        'title' => 'Sports News and Updates',
        'category' => 'Sports',
        'date' => 'November 03, 2026',
        'description' => 'Read the latest sports news and exciting updates.'
    ]
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Bookmarks | NSBM NewsDesk</title>

    <link rel="stylesheet" href="../../assets/css/auth.css">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7f6;
            color: #333;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }

        .page-title {
            color: #006747;
            margin-bottom: 10px;
        }

        .welcome {
            color: #666;
            margin-bottom: 30px;
        }

        .bookmark-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .bookmark-card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
        }

        .category {
            color: #006747;
            font-weight: bold;
            font-size: 13px;
        }

        .bookmark-card h3 {
            margin: 12px 0;
        }

        .date {
            color: #888;
            font-size: 13px;
        }

        .description {
            line-height: 1.6;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .buttons button {
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .read-btn {
            background: #006747;
            color: white;
        }

        .remove-btn {
            background: #ffe5e5;
            color: #cc3333;
        }

        .empty-message {
            display: none;
            background: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
        }

        @media (max-width: 600px) {
            .container {
                margin: 15px auto;
            }
        }
    </style>
</head>

<body>

    <main class="container">

        <h1 class="page-title">My Bookmarks</h1>

        <p class="welcome">
            Welcome, <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>!
            Here are your saved articles.
        </p>

        <div class="bookmark-grid" id="bookmarkGrid">

            <?php foreach ($bookmarks as $index => $article): ?>

                <div class="bookmark-card">

                    <span class="category">
                        <?= htmlspecialchars($article['category']) ?>
                    </span>

                    <h3>
                        <?= htmlspecialchars($article['title']) ?>
                    </h3>

                    <p class="date">
                        <?= htmlspecialchars($article['date']) ?>
                    </p>

                    <p class="description">
                        <?= htmlspecialchars($article['description']) ?>
                    </p>

                    <div class="buttons">

                        <a href="../article.php">
                            <button class="read-btn" type="button">
                                Read Article
                            </button>
                        </a>

                        <button
                            class="remove-btn"
                            type="button"
                            onclick="removeBookmark(this)">
                            Remove
                        </button>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <div class="empty-message" id="emptyMessage">
            <h3>No Bookmarks Found!</h3>
            <p>You have not saved any articles yet.</p>
        </div>

    </main>

    <script>
        function removeBookmark(button) {

            if (confirm("Are you sure you want to remove this bookmark?")) {

                button.closest(".bookmark-card").remove();

                const grid = document.getElementById("bookmarkGrid");

                if (grid.children.length === 0) {
                    document.getElementById("emptyMessage").style.display = "block";
                }
            }
        }
    </script>

</body>
</html>