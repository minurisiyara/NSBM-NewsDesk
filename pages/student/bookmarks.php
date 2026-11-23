<?php

require_once dirname(__DIR__, 2) . '/backend/config/session.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

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

// Get bookmarks from database
$userId = (int) $_SESSION['user_id'];

$sql = "
    SELECT
        b.id AS bookmark_id,
        a.id AS article_id,
        a.title,
        a.content AS description,
        c.name AS category,
        COALESCE(a.published_at, a.created_at) AS date
    FROM bookmarks b
    INNER JOIN articles a ON a.id = b.article_id
    LEFT JOIN categories c ON c.id = a.category_id
    WHERE b.user_id = :user_id
      AND a.status = 'published'
    ORDER BY b.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $userId]);

$bookmarks = $stmt->fetchAll();

foreach ($bookmarks as &$bookmark) {
    $bookmark['date'] = date(
        'F d, Y',
        strtotime($bookmark['date'])
    );
}
unset($bookmark);

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

            <?php if (!empty($bookmarks)): ?>

                <?php foreach ($bookmarks as $article): ?>

                    <div class="bookmark-card">

                        <span class="category">
                            <?= htmlspecialchars($article['category'] ?? 'General', ENT_QUOTES, 'UTF-8') ?>
                        </span>

                        <h3>
                            <?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?>
                        </h3>

                        <p class="date">
                            <?= htmlspecialchars($article['date'], ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <p class="description">
                            <?= htmlspecialchars($article['description'], ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <div class="buttons">

                            <a href="../article.php?id=<?= (int) $article['article_id'] ?>">
                                <button class="read-btn" type="button">
                                    Read Article
                                </button>
                            </a>

                            <button class="remove-btn"
        data-bookmark-id="<?= (int) $article['bookmark_id'] ?>"
        onclick="removeBookmark(this)">
    Remove
</button>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="empty-message" style="display: block;">
                    <h3>No Bookmarks Found!</h3>
                    <p>You have not saved any articles yet.</p>
                </div>

            <?php endif; ?>

        </div>

    </main>

    <script>
        const bookmarkCsrf = <?= json_encode($_SESSION['bookmark_csrf']) ?>;

async function removeBookmark(button) {

    if (!confirm("Are you sure you want to remove this bookmark?")) {
        return;
    }

    const bookmarkId = button.dataset.bookmarkId;

    try {
        const response = await fetch("../../backend/bookmarks/remove.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                bookmark_id: bookmarkId,
                csrf: bookmarkCsrf
            })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            alert(result.message || "Unable to remove bookmark.");
            return;
        }

        button.closest(".bookmark-card").remove();

        const grid = document.getElementById("bookmarkGrid");

        if (grid.querySelectorAll(".bookmark-card").length === 0) {
            grid.innerHTML = `
                <div class="empty-message" style="display: block;">
                    <h3>No Bookmarks Found!</h3>
                    <p>You have not saved any articles yet.</p>
                </div>
            `;
        }

    } catch (error) {
        alert("Something went wrong. Please try again.");
        console.error(error);
    }
}
    </script>

</body>
</html>