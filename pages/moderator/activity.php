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

// Pending reports badge count for sidebar.
$pendingReportsCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM content_reports WHERE status = 'pending'"
)->fetchColumn();

// Activity statistics.
$totalActions = (int) $pdo->query('SELECT COUNT(*) FROM moderation_logs')->fetchColumn();

$myActionsStmt = $pdo->prepare('SELECT COUNT(*) FROM moderation_logs WHERE moderator_id = :id');
$myActionsStmt->execute(['id' => $userId]);
$myActions = (int) $myActionsStmt->fetchColumn();

$todayActions = (int) $pdo->query(
    'SELECT COUNT(*) FROM moderation_logs WHERE DATE(created_at) = CURDATE()'
)->fetchColumn();

// Fetch recent moderation logs with related details.
$logsQuery = $pdo->query(
    'SELECT
        l.id,
        l.action,
        l.article_id,
        l.comment_id,
        l.report_id,
        l.reason,
        l.created_at,
        u.name AS moderator_name,
        a.title AS article_title,
        c.content AS comment_content
     FROM moderation_logs l
     INNER JOIN users u ON u.id = l.moderator_id
     LEFT JOIN articles a ON a.id = l.article_id
     LEFT JOIN comments c ON c.id = l.comment_id
     ORDER BY l.created_at DESC
     LIMIT 50'
);
$logs = $logsQuery->fetchAll();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function formatActionLabel(string $action): array
{
    $map = [
        'article_approved'        => ['label' => 'Article Approved',       'badge' => 'status-approved'],
        'article_rejected'        => ['label' => 'Article Rejected',       'badge' => 'status-removed'],
        'comment_approved'        => ['label' => 'Comment Approved',       'badge' => 'status-approved'],
        'comment_hidden'          => ['label' => 'Comment Hidden',         'badge' => 'status-hidden'],
        'comment_restored'        => ['label' => 'Comment Restored',       'badge' => 'status-approved'],
        'comment_removed'         => ['label' => 'Comment Removed',        'badge' => 'status-removed'],
        'report_review_started'   => ['label' => 'Report Review Started',  'badge' => 'status-reviewing'],
        'report_resolved_archive' => ['label' => 'Report Resolved (Article Archived)', 'badge' => 'status-resolved'],
        'report_resolved_hide'    => ['label' => 'Report Resolved (Comment Hidden)',   'badge' => 'status-resolved'],
        'report_resolved_remove'  => ['label' => 'Report Resolved (Comment Removed)',  'badge' => 'status-resolved'],
        'report_resolved_none'    => ['label' => 'Report Resolved (No Content Change)','badge' => 'status-resolved'],
        'report_dismissed'        => ['label' => 'Report Dismissed',       'badge' => 'status-dismissed'],
    ];

    return $map[$action] ?? [
        'label' => ucwords(str_replace('_', ' ', $action)),
        'badge' => 'status-pending',
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Desk Activity | NSBM NewsDesk</title>
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

            <a href="comments.php" class="nav-link">
                <span>▧</span> Comments
            </a>

            <a href="reports.php" class="nav-link">
                <span>⚑</span> Reports
                <span class="nav-count"><?= $pendingReportsCount ?></span>
            </a>

            <a href="activity.php" class="nav-link active">
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
                <button type="submit" class="logout-link" title="Log out">
                    ↗
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="moderator-main">
        <header class="topbar">
            <div>
                <span class="topbar-eyebrow">NSBM GREEN UNIVERSITY TOWN</span>
                <h1>Desk Activity</h1>
            </div>

            <div class="topbar-profile">
                <span class="profile-status"></span>
                <span><?= e($account['name']) ?></span>
            </div>
        </header>

        <div class="dashboard-content">
            <section class="page-intro">
                <div>
                    <span class="section-kicker">AUDIT TRAIL</span>
                    <h2>Moderation Activity Log</h2>
                    <p>
                        Complete history of article approvals, comment actions,
                        and report resolutions.
                    </p>
                </div>

                <div class="date-label">
                    <?= e(date('l, d F Y')) ?>
                </div>
            </section>

            <!-- STATISTICS -->
            <section class="statistics-grid">
                <article class="stat-card">
                    <span class="stat-label">Total Logged Actions</span>
                    <strong class="stat-number"><?= $totalActions ?></strong>
                    <span class="stat-description">All recorded desk actions</span>
                </article>

                <article class="stat-card stat-approved">
                    <span class="stat-label">Your Actions</span>
                    <strong class="stat-number"><?= $myActions ?></strong>
                    <span class="stat-description">Completed by <?= e($account['name']) ?></span>
                </article>

                <article class="stat-card stat-reviewed">
                    <span class="stat-label">Actions Today</span>
                    <strong class="stat-number"><?= $todayActions ?></strong>
                    <span class="stat-description">Recorded today</span>
                </article>
            </section>

            <!-- ACTIVITY LIST -->
            <section class="submissions-section">
                <div class="section-heading">
                    <div>
                        <span class="section-kicker">RECENT HISTORY</span>
                        <h2>Recent Desk Actions</h2>
                        <p>Showing the latest 50 moderation actions.</p>
                    </div>
                    <span class="queue-count"><?= count($logs) ?> entries</span>
                </div>

                <?php if (!$logs): ?>
                    <div class="empty-state">
                        <div class="empty-icon">◷</div>
                        <h3>No moderation activity recorded yet.</h3>
                        <p>Actions taken on articles, comments, and reports will appear here automatically.</p>
                    </div>
                <?php else: ?>
                    <div class="report-list">
                        <?php foreach ($logs as $log): ?>
                            <?php $meta = formatActionLabel($log['action']); ?>
                            <article class="report-card">
                                <div class="report-card-header">
                                    <div class="report-card-heading">
                                        <span class="report-type">Log #<?= (int) $log['id'] ?></span>
                                        <span class="report-id">by <?= e($log['moderator_name']) ?></span>
                                    </div>
                                    <span class="report-status <?= e($meta['badge']) ?>">
                                        <?= e($meta['label']) ?>
                                    </span>
                                </div>

                                <?php if ($log['article_id'] !== null): ?>
                                    <div class="reported-content">
                                        <span class="reported-content-label">Target Article</span>
                                        <h4><?= e($log['article_title'] ?? ('Article #' . (int) $log['article_id'])) ?></h4>
                                        <small>Article #<?= (int) $log['article_id'] ?></small>
                                    </div>
                                <?php endif; ?>

                                <?php if ($log['comment_id'] !== null): ?>
                                    <div class="reported-content">
                                        <span class="reported-content-label">Target Comment</span>
                                        <p><?= nl2br(e($log['comment_content'] ?? ('Comment #' . (int) $log['comment_id']))) ?></p>
                                        <small>Comment #<?= (int) $log['comment_id'] ?></small>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($log['reason'])): ?>
                                    <div class="report-resolution">
                                        <span>Recorded Note / Reason</span>
                                        <p><?= nl2br(e($log['reason'])) ?></p>
                                    </div>
                                <?php endif; ?>

                                <div class="report-meta">
                                    <span>
                                        <?php if ($log['report_id'] !== null): ?>
                                            Related Report: <strong>#<?= (int) $log['report_id'] ?></strong>
                                        <?php else: ?>
                                            Direct Moderation Action
                                        <?php endif; ?>
                                    </span>
                                    <span><?= e(date('d M Y, h:i A', strtotime($log['created_at']))) ?></span>
                                </div>
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