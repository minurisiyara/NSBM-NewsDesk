<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/backend/config/session.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';
require_once dirname(__DIR__, 2) . '/backend/config/auth.php';

requireRole(['moderator']);

$userId = (int) $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];

// Verify the moderator account.
$accountQuery = $pdo->prepare(
    'SELECT name, email, role, is_active
     FROM users
     WHERE id = :id
     LIMIT 1'
);
$accountQuery->execute(['id' => $userId]);
$account = $accountQuery->fetch();

if (
    !$account ||
    (int) $account['is_active'] !== 1 ||
    $account['role'] !== $userRole
) {
    http_response_code(403);
    exit('Your account is inactive or unavailable.');
}

// Generate a CSRF token.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Dashboard statistics.
$pendingQuery = $pdo->prepare(
    "SELECT COUNT(*)
     FROM articles
     WHERE status = 'pending'
       AND author_id != :user_id"
);
$pendingQuery->execute(['user_id' => $userId]);
$pendingCount = (int) $pendingQuery->fetchColumn();

$approvedCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM articles WHERE status = 'approved'"
)->fetchColumn();

$rejectedCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM articles WHERE status = 'rejected'"
)->fetchColumn();

$reviewedQuery = $pdo->prepare(
    "SELECT COUNT(*)
     FROM articles
     WHERE reviewed_by = :user_id
       AND DATE(reviewed_at) = CURDATE()"
);
$reviewedQuery->execute(['user_id' => $userId]);
$reviewedToday = (int) $reviewedQuery->fetchColumn();

$successMessage = $_GET['success'] ?? '';
$errorMessage = $_GET['error'] ?? '';

// Fetch pending articles, excluding the moderator's own submissions.
$articleQuery = $pdo->prepare(
    "SELECT
        a.id,
        a.title,
        a.content,
        a.created_at,
        u.name AS author_name,
        c.name AS category_name
     FROM articles a
     INNER JOIN users u ON u.id = a.author_id
     INNER JOIN categories c ON c.id = a.category_id
     WHERE a.status = 'pending'
       AND a.author_id != :user_id
     ORDER BY a.created_at ASC"
);

$articleQuery->execute(['user_id' => $userId]);
$articles = $articleQuery->fetchAll();

// Escape output before displaying it.
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Moderator Dashboard | NSBM NewsDesk</title>

    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/moderator.css">
</head>

<body class="moderator-page">

<div class="moderator-layout">

    <!-- SIDEBAR -->
    <aside class="moderator-sidebar">

        <a href="approvals.php" class="brand">
            <img
                src="../../assets/images/logo.png"
                alt="NSBM Green University Town"
                class="campus-logo"
            >

            <span class="brand-name">
                <strong>NSBM NewsDesk</strong>
                <small>Editorial Desk</small>
            </span>
        </a>

        <div class="sidebar-label">MODERATION</div>

        <nav class="sidebar-nav">
            <a href="#overview" class="nav-link active">
                <span>&#9638;</span> Overview
            </a>

            <a href="#submissions" class="nav-link">
                <span>&#9636;</span> Submissions
            </a>

            <a href="comments.php" class="nav-link">
                <span>&#9639;</span> Comments
            </a>

            <a href="reports.php" class="nav-link">
                <span>&#9873;</span> Reports
                <span class="nav-count"><?= $pendingReportsCount ?></span>
            </a>

            <a href="activity.php" class="nav-link">
                <span>&#9719;</span> Desk Activity
            </a>
        </nav>

        <div class="sidebar-bottom">
            <div class="moderator-avatar">
                <?= e(strtoupper(substr($account['name'], 0, 1))) ?>
            </div>

            <div class="moderator-identity">
                <strong><?= e($account['name']) ?></strong>
                <small>Moderator</small>
            </div>

<form method="post" action="../../backend/auth/logout.php">
    <input
        type="hidden"
        name="csrf_token"
        value="<?= e($_SESSION['csrf_token']) ?>"
    >
    <button type="submit" class="logout-link" title="Log out">&#8618;</button>
</form>
        </div>

    </aside>

    <!-- MAIN CONTENT -->
    <main class="moderator-main">

        <!-- TOP HEADER -->
        <header class="topbar">
            <div>
                <span class="topbar-eyebrow">NSBM GREEN UNIVERSITY TOWN</span>
                <h1>Editorial Desk</h1>
            </div>

            <div class="topbar-profile">
                <span class="profile-status"></span>
                <span><?= e($account['name']) ?></span>
            </div>
        </header>

        <div class="dashboard-content">

            <!-- PAGE INTRODUCTION -->
            <section class="page-intro" id="overview">
                <div>
                    <span class="section-kicker">MODERATION OVERVIEW</span>
                    <h2>Good day, <?= e($account['name']) ?>.</h2>
                    <p>
                        Review submissions and help maintain the quality
                        of campus news.
                    </p>
                </div>

                <div class="date-label">
                    <?= e(date('l, d F Y')) ?>
                </div>
            </section>

            <!-- FEEDBACK MESSAGES -->
            <?php if (is_string($successMessage) && $successMessage !== ''): ?>
                <div class="moderation-message success-message">
                    <?= e($successMessage) ?>
                </div>
            <?php endif; ?>

            <?php if (is_string($errorMessage) && $errorMessage !== ''): ?>
                <div class="moderation-message error-message">
                    <?= e($errorMessage) ?>
                </div>
            <?php endif; ?>

            <!-- STATISTICS -->
            <section class="statistics-grid">

                <article class="stat-card stat-pending">
                    <span class="stat-label">Pending Review</span>
                    <strong class="stat-number">
                        <?= $pendingCount ?>
                    </strong>
                    <span class="stat-description">
                        Awaiting moderation
                    </span>
                </article>

                <article class="stat-card stat-approved">
                    <span class="stat-label">Approved Articles</span>
                    <strong class="stat-number">
                        <?= $approvedCount ?>
                    </strong>
                    <span class="stat-description">
                        Successfully reviewed
                    </span>
                </article>

                <article class="stat-card stat-rejected">
                    <span class="stat-label">Rejected Articles</span>
                    <strong class="stat-number">
                        <?= $rejectedCount ?>
                    </strong>
                    <span class="stat-description">
                        Returned to authors
                    </span>
                </article>

                <article class="stat-card stat-reviewed">
                    <span class="stat-label">Reviewed Today</span>
                    <strong class="stat-number">
                        <?= $reviewedToday ?>
                    </strong>
                    <span class="stat-description">
                        Your moderation activity
                    </span>
                </article>

            </section>

            <!-- ARTICLE SUBMISSIONS -->
            <section class="submissions-section" id="submissions">

                <div class="section-heading">
                    <div>
                        <span class="section-kicker">EDITORIAL QUEUE</span>
                        <h2>Article Submissions</h2>
                        <p>Review articles submitted by students.</p>
                    </div>

                    <span class="queue-count">
                        <?= $pendingCount ?> awaiting review
                    </span>
                </div>

                <?php if (!$articles): ?>

                    <div class="empty-state">
                        <div class="empty-icon">&#10003;</div>
                        <h3>You're all caught up.</h3>
                        <p>
                            There are currently no pending articles
                            available for your review.
                        </p>
                    </div>

                <?php else: ?>

                    <div class="article-list">

                        <?php foreach ($articles as $article): ?>

                            <article class="article-card">

                                <div class="article-card-top">
                                    <span class="article-category">
                                        <?= e($article['category_name']) ?>
                                    </span>

                                    <span class="article-status">
                                        Pending
                                    </span>
                                </div>

                                <h3><?= e($article['title']) ?></h3>

                                <p class="article-excerpt">
                                    <?= e(mb_strimwidth(
                                        $article['content'],
                                        0,
                                        260,
                                        '...'
                                    )) ?>
                                </p>

                                <div class="article-meta">
                                    <span>
                                        By <?= e($article['author_name']) ?>
                                    </span>

                                    <span>
                                        <?= e(date(
                                            'd M Y, h:i A',
                                            strtotime($article['created_at'])
                                        )) ?>
                                    </span>
                                </div>

                                <div class="article-actions">

                                    <form
                                        method="post"
                                        action="../../backend/news/update.php"
                                        class="moderation-form"
                                        onsubmit="return confirm('Approve this article?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e($_SESSION['csrf_token']) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="article_id"
                                            value="<?= (int) $article['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="action"
                                            value="approve"
                                            class="action-button approve-button"
                                        >
                                            Approve Article
                                        </button>
                                    </form>


                                    <form
                                        method="post"
                                        action="../../backend/news/update.php"
                                        class="moderation-form rejection-form"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e($_SESSION['csrf_token']) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="article_id"
                                            value="<?= (int) $article['id'] ?>"
                                        >

                                        <details class="rejection-details">

                                            <summary class="reject-toggle">
                                                Reject Article
                                            </summary>

                                            <div class="rejection-content">

                                                <label for="reason-<?= (int) $article['id'] ?>">
                                                    Reason for rejection
                                                </label>

                                                <textarea
                                                    id="reason-<?= (int) $article['id'] ?>"
                                                    name="rejection_reason"
                                                    maxlength="2000"
                                                    required
                                                    placeholder="Explain what needs to be corrected in this article..."
                                                ></textarea>

                                                <button
                                                    type="submit"
                                                    name="action"
                                                    value="reject"
                                                    class="action-button reject-button"
                                                    onclick="return confirm('Are you sure you want to reject this article?');"
                                                >
                                                    Confirm Rejection
                                                </button>

                                            </div>

                                        </details>

                                    </form>


                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </section>



        </div>

        <footer class="moderator-footer">
            NSBM NewsDesk &#183; Editorial Moderation
        </footer>

    </main>

</div>

</body>
</html>