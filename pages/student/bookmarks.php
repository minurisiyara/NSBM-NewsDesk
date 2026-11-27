<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/backend/config/session.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Check student role
if (($_SESSION['user_role'] ?? '') !== 'student') {
    http_response_code(403);
    exit('Access denied.');
}

// Student details
$userId = (int) $_SESSION['user_id'];

// Active sidebar page
$activePage = 'bookmarks';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get bookmarked articles
$stmt = $pdo->prepare("
    SELECT
        a.id,
        a.title,
        a.content,
        a.image_path,
        a.created_at,
        c.name AS category
    FROM bookmarks b
    INNER JOIN articles a ON b.article_id = a.id
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");

$stmt->execute([$userId]);

$bookmarks = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Bookmarks | NSBM NewsDesk</title>

    <!-- Main Student CSS -->
    <link rel="stylesheet" href="../../assets/css/student.css">

    <!-- Bookmarks CSS -->
    <link rel="stylesheet" href="../../assets/css/bookmarks.css">

    <!-- Student Logo CSS -->
    <link rel="stylesheet" href="../../assets/css/student-logo.css?v=3">

    <!-- Student Topbar CSS -->
    <link rel="stylesheet" href="../../assets/css/student-topbar.css?v=2">

</head>

<body>

    <div class="dashboard-container">

        <!-- Common Student Sidebar -->

        <?php
        include dirname(__DIR__, 2) . '/components/student-sidebar.php';
        ?>


        <!-- Main Content -->

        <main class="main-content">


            <!-- Common Student Topbar -->

            <?php
            include dirname(__DIR__, 2) . '/components/student-topbar.php';
            ?>


            <!-- Bookmarks Content -->

            <section class="bookmarks-content">


                <!-- Page Heading -->

                <div class="bookmarks-heading">

                    <h2>Saved Articles</h2>

                    <p>
                        Articles you have saved for later.
                    </p>

                </div>


                <?php if (!empty($bookmarks)): ?>


                    <!-- Bookmarks Grid -->

                    <div class="bookmarks-grid">


                        <?php foreach ($bookmarks as $article): ?>


                            <div class="bookmark-card">


                                <!-- Article Image -->

                                <?php if (!empty($article['image_path'])): ?>

                                    <img
                                        src="../../<?= htmlspecialchars(
                                            $article['image_path'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        alt="Article Image"
                                        class="bookmark-image"
                                    >

                                <?php endif; ?>


                                <!-- Article Details -->

                                <div class="bookmark-details">


                                    <!-- Category -->

                                    <span class="bookmark-category">

                                        <?= htmlspecialchars(
                                            $article['category'] ?? 'General',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </span>


                                    <!-- Article Title -->

                                    <h3>

                                        <?= htmlspecialchars(
                                            $article['title'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </h3>


                                    <!-- Article Content -->

                                    <p>

                                        <?= htmlspecialchars(
                                            substr($article['content'], 0, 150),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>...

                                    </p>


                                    <!-- Article Date -->

                                    <small>

                                        <?= htmlspecialchars(
                                            date(
                                                'M d, Y',
                                                strtotime($article['created_at'])
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </small>


                                </div>

                            </div>


                        <?php endforeach; ?>


                    </div>


                <?php else: ?>


                    <!-- Empty State -->

                    <div class="bookmark-empty">

                        <h3>No Bookmarks Yet</h3>

                        <p>
                            You haven't saved any articles yet.
                        </p>

                        <a href="../../index.php">
                            Explore Articles
                        </a>

                    </div>


                <?php endif; ?>


            </section>


        </main>

    </div>

</body>

</html>