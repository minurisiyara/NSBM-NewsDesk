<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/backend/config/session.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';
require_once dirname(__DIR__, 2) . '/backend/config/auth.php';

requireRole(['moderator']);

$userId = (int) $_SESSION['user_id'];

// Verify moderator account.
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
    $account['role'] !== 'moderator'
) {
    http_response_code(403);
    exit('Your moderator account is inactive or unavailable.');
}

// Generate CSRF token.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Available filters.
$allowedStatuses = ['pending', 'approved', 'hidden', 'removed'];

$status = $_GET['status'] ?? 'pending';

if (!is_string($status) || !in_array($status, $allowedStatuses, true)) {
    $status = 'pending';
}

// Count comments in each status.
$statsQuery = $pdo->query(
    "SELECT
        COUNT(*) AS total,
        SUM(status = 'pending') AS pending,
        SUM(status = 'approved') AS approved,
        SUM(status = 'hidden') AS hidden,
        SUM(status = 'removed') AS removed
     FROM comments"
);

$stats = $statsQuery->fetch();

// Fetch comments for the selected filter.
$commentQuery = $pdo->prepare(
    'SELECT
        c.id,
        c.content,
        c.status,
        c.created_at,
        c.moderated_at,
        c.article_id,
        c.user_id,
        u.name AS student_name,
        a.title AS article_title
     FROM comments c
     INNER JOIN users u ON c.user_id = u.id
     INNER JOIN articles a ON c.article_id = a.id
     WHERE c.status = :status
     ORDER BY c.created_at DESC'
);

$commentQuery->execute(['status' => $status]);
$comments = $commentQuery->fetchAll();

// Escape output.
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Display names for statuses.
$statusLabels = [
    'pending' => 'Pending',
    'approved' => 'Approved',
    'hidden' => 'Hidden',
    'removed' => 'Removed',
];

$successMessage = $_GET['success'] ?? '';
$errorMessage = $_GET['error'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Comments Moderation | NSBM NewsDesk</title>

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

            <a href="approvals.php" class="nav-link">
                <span>▦</span> Overview
            </a>

            <a href="approvals.php#submissions" class="nav-link">
                <span>▤</span> Submissions
            </a>

            <a href="comments.php" class="nav-link active">
                <span>▧</span> Comments
                <span class="nav-count">
                    <?= (int) $stats['pending'] ?>
                </span>
            </a>

            <a href="reports.php" class="nav-link">
                <span>⚑</span> Reports
            </a>

            <a href="activity.php" class="nav-link">
                <span>◷</span> Desk Activity
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

        <header class="topbar">

            <div>
                <span class="topbar-eyebrow">
                    NSBM GREEN UNIVERSITY TOWN
                </span>

                <h1>Comments Desk</h1>
            </div>

            <div class="topbar-profile">
                <span class="profile-status"></span>
                <span><?= e($account['name']) ?></span>
            </div>

        </header>

        <div class="dashboard-content">

            <!-- PAGE INTRO -->
            <section class="page-intro">

                <div>
                    <span class="section-kicker">
                        COMMUNITY MODERATION
                    </span>

                    <h2>Comments Management</h2>

                    <p>
                        Review and manage discussions across campus news.
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
                    <span class="stat-label">Pending Comments</span>

                    <strong class="stat-number">
                        <?= (int) $stats['pending'] ?>
                    </strong>

                    <span class="stat-description">
                        Awaiting moderation
                    </span>
                </article>

                <article class="stat-card stat-approved">
                    <span class="stat-label">Approved Comments</span>

                    <strong class="stat-number">
                        <?= (int) $stats['approved'] ?>
                    </strong>

                    <span class="stat-description">
                        Visible discussions
                    </span>
                </article>

                <article class="stat-card stat-reviewed">
                    <span class="stat-label">Hidden Comments</span>

                    <strong class="stat-number">
                        <?= (int) $stats['hidden'] ?>
                    </strong>

                    <span class="stat-description">
                        Temporarily hidden
                    </span>
                </article>

                <article class="stat-card stat-rejected">
                    <span class="stat-label">Removed Comments</span>

                    <strong class="stat-number">
                        <?= (int) $stats['removed'] ?>
                    </strong>

                    <span class="stat-description">
                        Removed from discussions
                    </span>
                </article>

            </section>

            <!-- COMMENT QUEUE -->
            <section class="submissions-section">

                <div class="section-heading">

                    <div>
                        <span class="section-kicker">
                            COMMUNITY DISCUSSIONS
                        </span>

                        <h2>Comment Queue</h2>

                        <p>
                            Review comments and take appropriate moderation actions.
                        </p>
                    </div>

                    <span class="queue-count">
                        <?= count($comments) ?> comments
                    </span>

                </div>

                <!-- STATUS FILTERS -->
                <nav class="comment-filters">

                    <?php foreach ($allowedStatuses as $filter): ?>

                        <a
                            href="comments.php?status=<?= e($filter) ?>"
                            class="comment-filter <?= $status === $filter ? 'active' : '' ?>"
                        >
                            <?= e($statusLabels[$filter]) ?>

                            <span>
                                <?= (int) $stats[$filter] ?>
                            </span>
                        </a>

                    <?php endforeach; ?>

                </nav>

                <!-- COMMENTS LIST -->
                <?php if (!$comments): ?>

                    <div class="empty-state">

                        <div class="empty-icon">✓</div>

                        <h3>No <?= e($status) ?> comments.</h3>

                        <p>
                            There are currently no comments in this category.
                        </p>

                    </div>

                <?php else: ?>

                    <div class="comment-list">

                        <?php foreach ($comments as $comment): ?>

                            <article class="comment-card">

                                <div class="comment-card-top">

                                    <span class="comment-student">
                                        <?= e($comment['student_name']) ?>
                                    </span>

                                    <span class="comment-status status-<?= e($comment['status']) ?>">
                                        <?= e($statusLabels[$comment['status']]) ?>
                                    </span>

                                </div>

                                <p class="comment-content">
                                    <?= nl2br(e($comment['content'])) ?>
                                </p>

                                <div class="comment-article">

                                    <span>Article</span>

                                    <strong>
                                        <?= e($comment['article_title']) ?>
                                    </strong>

                                </div>

                                <div class="comment-meta">

                                    <span>
                                        Comment #<?= (int) $comment['id'] ?>
                                    </span>

                                    <span>
                                        <?= e(date(
                                            'd M Y, h:i A',
                                            strtotime($comment['created_at'])
                                        )) ?>
                                    </span>

                                </div>

                                <!-- MODERATION ACTIONS -->
                                <?php if ($comment['status'] !== 'removed'): ?>

                                    <div class="comment-actions">

                                        <?php if ($comment['status'] === 'pending'): ?>

                                            <form
                                                method="post"
                                                action="../../backend/comments/moderate.php"
                                                onsubmit="return confirm('Approve this comment?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= e($_SESSION['csrf_token']) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="comment_id"
                                                    value="<?= (int) $comment['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    name="action"
                                                    value="approve"
                                                    class="action-button approve-button"
                                                >
                                                    Approve
                                                </button>

                                            </form>

                                        <?php endif; ?>

                                        <?php if (in_array($comment['status'], ['pending', 'approved'], true)): ?>

                                            <form
                                                method="post"
                                                action="../../backend/comments/moderate.php"
                                                onsubmit="return confirm('Hide this comment?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= e($_SESSION['csrf_token']) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="comment_id"
                                                    value="<?= (int) $comment['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    name="action"
                                                    value="hide"
                                                    class="action-button secondary-button"
                                                >
                                                    Hide
                                                </button>

                                            </form>

                                        <?php endif; ?>

                                        <?php if ($comment['status'] === 'hidden'): ?>

                                            <form
                                                method="post"
                                                action="../../backend/comments/moderate.php"
                                                onsubmit="return confirm('Restore this comment?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= e($_SESSION['csrf_token']) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="comment_id"
                                                    value="<?= (int) $comment['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    name="action"
                                                    value="restore"
                                                    class="action-button approve-button"
                                                >
                                                    Restore Comment
                                                </button>

                                            </form>

                                        <?php endif; ?>

                                        <form
                                            method="post"
                                            action="../../backend/comments/moderate.php"
                                            onsubmit="return confirm('Permanently remove this comment from public discussions?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= e($_SESSION['csrf_token']) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="comment_id"
                                                value="<?= (int) $comment['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="action"
                                                value="remove"
                                                class="action-button reject-button"
                                            >
                                                Remove
                                            </button>

                                        </form>

                                    </div>

                                <?php else: ?>

                                    <p class="removed-notice">
                                        This comment has been removed and cannot be restored through this page.
                                    </p>

                                <?php endif; ?>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </section>

        </div>

        <footer class="moderator-footer">
            NSBM NewsDesk · Editorial Moderation
        </footer>

    </main>

</div>

</body>
</html>
