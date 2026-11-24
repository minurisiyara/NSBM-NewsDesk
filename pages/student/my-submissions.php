<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/backend/config/session.php';

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
$name = $_SESSION['user_name'] ?? 'Student';
$userId = (int) $_SESSION['user_id'];

// Database connection
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

// Get student's articles
$stmt = $pdo->prepare("
    SELECT
        a.title,
        a.status,
        a.rejection_reason,
        a.created_at,
        c.name AS category
    FROM articles a
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE a.author_id = ?
    ORDER BY a.created_at DESC
");

$stmt->execute([$userId]);

$articles = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Submissions | NSBM NewsDesk</title>

    <link rel="stylesheet" href="../../assets/css/student.css">

</head>

<body>

    <!-- Sidebar -->

    <aside class="sidebar">

        <div class="brand">

            <div class="brand-icon">N</div>

            <div>
                <h2>NSBM</h2>
                <span>NewsDesk Portal</span>
            </div>

        </div>

        <nav class="sidebar-nav">

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="my-submissions.php" class="active">
                My Submissions
            </a>

            <a href="submit-article.php">
                Submit Article
            </a>

            <a href="bookmarks.php">
                Saved Research
            </a>

            <a href="profile.php">
                Scholar Profile
            </a>

        </nav>

        <div class="sidebar-bottom">

            <a href="../../index.php">
                Back to Journal
            </a>

            <span>NSBM Green University</span>

        </div>

    </aside>


    <!-- Main Content -->

    <div class="main-content">

        <!-- Topbar -->

        <header class="topbar">

            <span class="portal-label">STUDENT PORTAL</span>

            <div class="topbar-right">

                <span>
                    <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                </span>

                <form action="../../backend/auth/logout.php" method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                            $_SESSION['csrf_token'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >

                    <button type="submit" class="logout-button">
                        Log Out
                    </button>

                </form>

            </div>

        </header>


        <!-- Page Content -->

        <main class="dashboard-content">


            <!-- Welcome Banner -->

            <section class="welcome-banner">

                <span class="welcome-label">
                    STUDENT PORTAL
                </span>

                <h1>My Submissions</h1>

                <p>
                    Hello, <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>!
                    Track your submitted articles and check their editorial
                    progress through NSBM NewsDesk.
                </p>

                <div class="welcome-actions">

                    <a href="dashboard.php" class="secondary-button">
                        Back to Dashboard
                    </a>

                    <a href="submit-article.php" class="primary-button">
                        + Submit New Article
                    </a>

                </div>

            </section>


            <!-- Articles Section -->

            <section class="recent-section">

                <div class="section-heading">

                    <div>

                        <span>ARTICLE MANAGEMENT</span>

                        <h2>My Articles</h2>

                    </div>

                    <span>
                        Total: <?= count($articles) ?> Articles
                    </span>

                </div>


                <?php if (!empty($articles)): ?>

                    <div class="table-container">

                        <table>

                            <thead>

                                <tr>

                                    <th>Article Title</th>

                                    <th>Category</th>

                                    <th>Status</th>

                                    <th>Submitted Date</th>

                                    <th>Feedback</th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php foreach ($articles as $article): ?>

                                    <tr>

                                        <!-- Article Title -->

                                        <td>

                                            <?= htmlspecialchars(
                                                $article['title'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </td>


                                        <!-- Category -->

                                        <td>

                                            <?= htmlspecialchars(
                                                $article['category'] ?? 'Uncategorized',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </td>


                                        <!-- Status -->

                                        <td>

                                            <span class="status-badge <?= htmlspecialchars(
                                                $article['status'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>">

                                                <?= htmlspecialchars(
                                                    ucfirst($article['status']),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </span>

                                        </td>


                                        <!-- Submitted Date -->

                                        <td>

                                            <?= htmlspecialchars(
                                                date(
                                                    'M d, Y',
                                                    strtotime($article['created_at'])
                                                ),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </td>


                                        <!-- Feedback -->

                                        <td>

                                            <?php if ($article['status'] === 'rejected'): ?>

                                                <?= htmlspecialchars(
                                                    $article['rejection_reason'] ?? 'No reason provided.',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            <?php else: ?>

                                                -

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <!-- Empty State -->

                    <div class="empty-state">

                        <h3>No Articles Submitted Yet</h3>

                        <p>
                            You haven't submitted any articles yet.
                            Start your academic publishing journey
                            by submitting your first article.
                        </p>

                        <a href="submit-article.php" class="primary-button">
                            + Submit Your First Article
                        </a>

                    </div>


                <?php endif; ?>

            </section>

        </main>

    </div>

</body>

</html>