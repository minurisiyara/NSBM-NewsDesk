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

// Available report statuses.
$allowedStatuses = [
    'all',
    'pending',
    'reviewing',
    'resolved',
    'dismissed'
];

$statusLabels = [
    'pending' => 'Pending',
    'reviewing' => 'Reviewing',
    'resolved' => 'Resolved',
    'dismissed' => 'Dismissed'
];

$status = $_GET['status'] ?? 'pending';

if (
    !is_string($status) ||
    !in_array($status, $allowedStatuses, true)
) {
    $status = 'pending';
}

// Report statistics.
$statsQuery = $pdo->query(
    "SELECT
        COUNT(*) AS total,
        COALESCE(SUM(status = 'pending'), 0) AS pending,
        COALESCE(SUM(status = 'reviewing'), 0) AS reviewing,
        COALESCE(SUM(status = 'resolved'), 0) AS resolved,
        COALESCE(SUM(status = 'dismissed'), 0) AS dismissed
     FROM content_reports"
);

$stats = $statsQuery->fetch();

// Fetch reports with related article, comment and user details.
$sql = '
    SELECT
        r.id,
        r.reporter_id,
        r.target_type,
        r.article_id,
        r.comment_id,
        r.reason,
        r.description,
        r.status,
        r.reviewed_by,
        r.reviewed_at,
        r.resolution_note,
        r.created_at,

        reporter.name AS reporter_name,

        reviewer.name AS reviewer_name,

        a.title AS article_title,
        a.content AS article_content,
        a.status AS article_status,
        article_author.name AS article_author,

        c.content AS comment_content,
        c.status AS comment_status,
        comment_author.name AS comment_author

    FROM content_reports r

    INNER JOIN users reporter
        ON reporter.id = r.reporter_id

    LEFT JOIN users reviewer
        ON reviewer.id = r.reviewed_by

    LEFT JOIN articles a
        ON a.id = r.article_id

    LEFT JOIN users article_author
        ON article_author.id = a.author_id

    LEFT JOIN comments c
        ON c.id = r.comment_id

    LEFT JOIN users comment_author
        ON comment_author.id = c.user_id
';

// Apply selected status filter.
if ($status !== 'all') {
    $sql .= ' WHERE r.status = :status';
}

$sql .= ' ORDER BY r.created_at DESC';

$reportQuery = $pdo->prepare($sql);

if ($status !== 'all') {
    $reportQuery->execute(['status' => $status]);
} else {
    $reportQuery->execute();
}

$reports = $reportQuery->fetchAll();

// Escape output.
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$resultMessages = [
    'review_started'   => ['type' => 'success', 'text' => 'Report marked as reviewing. You can now investigate and resolve or dismiss it.'],
    'report_resolved'  => ['type' => 'success', 'text' => 'Report has been resolved and your resolution note was saved.'],
    'report_dismissed' => ['type' => 'success', 'text' => 'Report has been dismissed as no violation.'],
    'invalid_request'  => ['type' => 'error',   'text' => 'Invalid request method.'],
    'invalid_token'    => ['type' => 'error',   'text' => 'Security verification failed. Please refresh and try again.'],
    'invalid_report'   => ['type' => 'error',   'text' => 'Invalid report selected.'],
    'invalid_action'   => ['type' => 'error',   'text' => 'Invalid moderation action.'],
    'note_required'    => ['type' => 'error',   'text' => 'Please enter a resolution or dismissal note before submitting.'],
    'note_too_long'    => ['type' => 'error',   'text' => 'Resolution note cannot exceed 2,000 characters.'],
    'report_not_found' => ['type' => 'error',   'text' => 'The selected report could not be found.'],
    'invalid_status'   => ['type' => 'error',   'text' => 'This report has already been updated or cannot perform that action.'],
    'database_error'   => ['type' => 'error',   'text' => 'A database error occurred while updating the report. Please try again.'],
];

$resultCode = $_GET['result'] ?? '';
$successMessage = $_GET['success'] ?? '';
$errorMessage = $_GET['error'] ?? '';

if (is_string($resultCode) && isset($resultMessages[$resultCode])) {
    if ($resultMessages[$resultCode]['type'] === 'success') {
        $successMessage = $resultMessages[$resultCode]['text'];
    } else {
        $errorMessage = $resultMessages[$resultCode]['text'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Reports Desk | NSBM NewsDesk</title>

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
                <span>▪</span> Overview
            </a>

            <a href="approvals.php#submissions" class="nav-link">
                <span>¤</span> Submissions
            </a>

            <a href="comments.php" class="nav-link">
                <span>▧</span> Comments
            </a>

            <a href="reports.php" class="nav-link active">
                <span>⚑</span> Reports

                <span class="nav-count">
                    <?= (int) $stats['pending'] ?>
                </span>
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

                <h1>Reports Desk</h1>
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
                        COMMUNITY SAFETY
                    </span>

                    <h2>Reported Content</h2>

                    <p>
                        Review reported articles and comments to maintain
                        a safe and respectful campus community.
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

            <!-- REPORT STATISTICS -->
            <section class="statistics-grid">

                <article class="stat-card">
                    <span class="stat-label">Total Reports</span>

                    <strong class="stat-number">
                        <?= (int) $stats['total'] ?>
                    </strong>

                    <span class="stat-description">
                        All submitted reports
                    </span>
                </article>

                <article class="stat-card stat-pending">
                    <span class="stat-label">Pending</span>

                    <strong class="stat-number">
                        <?= (int) $stats['pending'] ?>
                    </strong>

                    <span class="stat-description">
                        Awaiting review
                    </span>
                </article>

                <article class="stat-card stat-reviewed">
                    <span class="stat-label">Reviewing</span>

                    <strong class="stat-number">
                        <?= (int) $stats['reviewing'] ?>
                    </strong>

                    <span class="stat-description">
                        Currently investigating
                    </span>
                </article>

                <article class="stat-card stat-approved">
                    <span class="stat-label">Resolved</span>

                    <strong class="stat-number">
                        <?= (int) $stats['resolved'] ?>
                    </strong>

                    <span class="stat-description">
                        Actions completed
                    </span>
                </article>

                <article class="stat-card stat-rejected">
                    <span class="stat-label">Dismissed</span>

                    <strong class="stat-number">
                        <?= (int) $stats['dismissed'] ?>
                    </strong>

                    <span class="stat-description">
                        No violation found
                    </span>
                </article>

            </section>

            <!-- REPORT QUEUE -->
            <section class="submissions-section">

                <div class="section-heading">

                    <div>

                        <span class="section-kicker">
                            COMMUNITY SAFETY
                        </span>

                        <h2>Report Queue</h2>

                        <p>
                            Investigate reported content and record
                            appropriate moderation decisions.
                        </p>

                    </div>

                    <span class="queue-count">
                        <?= count($reports) ?> reports
                    </span>

                </div>

                <!-- STATUS FILTERS -->
                <nav class="comment-filters">

                    <?php foreach ($allowedStatuses as $filter): ?>

                        <a
                            href="reports.php?status=<?= e($filter) ?>"
                            class="comment-filter <?= $status === $filter ? 'active' : '' ?>"
                        >

                            <?= $filter === 'all'
                                ? 'All Reports'
                                : e($statusLabels[$filter]) ?>

                            <span>
                                <?= (int) $stats[
                                    $filter === 'all' ? 'total' : $filter
                                ] ?>
                            </span>

                        </a>

                    <?php endforeach; ?>

                </nav>

                <!-- REPORT LIST -->
                <?php if (!$reports): ?>

                    <div class="empty-state">

                        <div class="empty-icon">✓</div>

                        <h3>No <?= e($status) ?> reports.</h3>

                        <p>
                            There are currently no reports in this category.
                        </p>

                    </div>

                <?php else: ?>

                    <div class="report-list">

                        <?php foreach ($reports as $report): ?>

                            <article class="report-card">

                                <!-- CARD HEADER -->
                                <div class="report-card-header">

                                    <div class="report-card-heading">

                                        <span class="report-type">
                                            <?= e(ucfirst($report['target_type'])) ?> Report
                                        </span>

                                        <span class="report-id">
                                            #<?= (int) $report['id'] ?>
                                        </span>

                                    </div>

                                    <span class="report-status status-<?= e($report['status']) ?>">
                                        <?= e($statusLabels[$report['status']] ?? $report['status']) ?>
                                    </span>

                                </div>

                                <!-- REPORT REASON -->
                                <div class="report-reason">

                                    <span>Reported for</span>

                                    <h3>
                                        <?= e($report['reason']) ?>
                                    </h3>

                                </div>

                                <!-- REPORTED CONTENT -->
                                <div class="reported-content">

                                    <span class="reported-content-label">
                                        Reported Content
                                    </span>

                                    <?php if ($report['target_type'] === 'article'): ?>

                                        <h4>
                                            <?= e($report['article_title'] ?? 'Article unavailable') ?>
                                        </h4>

                                        <?php
                                        $articleContent = strip_tags(
                                            (string) ($report['article_content'] ?? '')
                                        );
                                        ?>

                                        <p>
                                            <?= nl2br(e(substr($articleContent, 0, 250))) ?>
                                            <?= strlen($articleContent) > 250 ? '...' : '' ?>
                                        </p>

                                        <small>
                                            Article by
                                            <?= e($report['article_author'] ?? 'Unknown author') ?>
                                            · Article #<?= (int) $report['article_id'] ?>
                                            · Status: <strong><?= e(ucfirst($report['article_status'] ?? 'unknown')) ?></strong>
                                        </small>

                                    <?php else: ?>

                                        <p>
                                            <?= nl2br(e($report['comment_content'] ?? 'Comment unavailable')) ?>
                                        </p>

                                        <small>
                                            Comment by
                                            <?= e($report['comment_author'] ?? 'Unknown author') ?>
                                            · Comment #<?= (int) $report['comment_id'] ?>
                                            · Status: <strong><?= e(ucfirst($report['comment_status'] ?? 'unknown')) ?></strong>
                                        </small>

                                    <?php endif; ?>

                                </div>

                                <!-- REPORT DESCRIPTION -->
                                <div class="report-description">

                                    <span>Report Description</span>

                                    <p>
                                        <?= nl2br(e($report['description'])) ?>
                                    </p>

                                </div>

                                <!-- REPORT INFORMATION -->
                                <div class="report-meta">

                                    <span>
                                        Reported by:
                                        <strong>
                                            <?= e($report['reporter_name']) ?>
                                        </strong>
                                    </span>

                                    <span>
                                        <?= e(date(
                                            'd M Y, h:i A',
                                            strtotime($report['created_at'])
                                        )) ?>
                                    </span>

                                </div>

                                <!-- REVIEW HISTORY -->
                                <?php if ($report['reviewed_by'] !== null): ?>

                                    <div class="report-review-history">

                                        <span>
                                            Reviewed by:
                                            <strong>
                                                <?= e($report['reviewer_name'] ?? 'Unknown moderator') ?>
                                            </strong>
                                        </span>

                                        <?php if ($report['reviewed_at']): ?>

                                            <span>
                                                <?= e(date(
                                                    'd M Y, h:i A',
                                                    strtotime($report['reviewed_at'])
                                                )) ?>
                                            </span>

                                        <?php endif; ?>

                                    </div>

                                <?php endif; ?>

                                <!-- RESOLUTION NOTE -->
                                <?php if (
                                    in_array($report['status'], ['resolved', 'dismissed'], true)
                                    && !empty($report['resolution_note'])
                                ): ?>

                                    <div class="report-resolution">

                                        <span>Resolution Note</span>

                                        <p>
                                            <?= nl2br(e($report['resolution_note'])) ?>
                                        </p>

                                    </div>

                                <?php endif; ?>

                                <!-- MODERATION ACTIONS -->
                                <?php if ($report['status'] === 'pending'): ?>

                                    <div class="report-actions">

                                        <form
                                            method="post"
                                            action="../../backend/reports/review.php"
                                            onsubmit="return confirm('Start reviewing this report?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= e($_SESSION['csrf_token']) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="report_id"
                                                value="<?= (int) $report['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="action"
                                                value="start_review"
                                                class="action-button approve-button"
                                            >
                                                Start Review
                                            </button>

                                        </form>

                                    </div>

                                <?php elseif ($report['status'] === 'reviewing'): ?>

                                    <div class="report-actions report-final-actions">

                                        <form
                                            method="post"
                                            action="../../backend/reports/review.php"
                                            class="report-resolution-form"
                                            onsubmit="return confirm('Mark this report as resolved?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= e($_SESSION['csrf_token']) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="report_id"
                                                value="<?= (int) $report['id'] ?>"
                                            >
                                            <label for="content-action-<?= (int) $report['id'] ?>">
                                                Action on Reported <?= e(ucfirst($report['target_type'])) ?>
                                            </label>

                                            <select
                                                id="content-action-<?= (int) $report['id'] ?>"
                                                name="content_action"
                                                required
                                            >
                                                <?php if ($report['target_type'] === 'article'): ?>
                                                    <option value="archive">Archive Article (Unpublish from public feed)</option>
                                                    <option value="none">No content change (Keep article as-is)</option>
                                                <?php else: ?>
                                                    <option value="hide">Hide Comment</option>
                                                    <option value="remove">Remove Comment</option>
                                                    <option value="none">No content change (Keep comment as-is)</option>
                                                <?php endif; ?>
                                            </select>

                                            <label for="resolve-note-<?= (int) $report['id'] ?>">
                                                Resolution Note
                                            </label>

                                            <textarea
                                                id="resolve-note-<?= (int) $report['id'] ?>"
                                                name="resolution_note"
                                                rows="3"
                                                required
                                                placeholder="Describe the moderation action taken..."
                                            ></textarea>

                                            <button
                                                type="submit"
                                                name="action"
                                                value="resolve"
                                                class="action-button approve-button"
                                            >
                                                Resolve Report
                                            </button>

                                        </form>

                                        <form
                                            method="post"
                                            action="../../backend/reports/review.php"
                                            class="report-resolution-form"
                                            onsubmit="return confirm('Dismiss this report?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= e($_SESSION['csrf_token']) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="report_id"
                                                value="<?= (int) $report['id'] ?>"
                                            >

                                            <label for="dismiss-note-<?= (int) $report['id'] ?>">
                                                Dismissal Reason
                                            </label>

                                            <textarea
                                                id="dismiss-note-<?= (int) $report['id'] ?>"
                                                name="resolution_note"
                                                rows="3"
                                                required
                                                placeholder="Explain why this report is being dismissed..."
                                            ></textarea>

                                            <button
                                                type="submit"
                                                name="action"
                                                value="dismiss"
                                                class="action-button reject-button"
                                            >
                                                Dismiss Report
                                            </button>

                                        </form>

                                    </div>

                                <?php else: ?>

                                    <p class="report-closed-notice">
                                        This report has been closed.
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
