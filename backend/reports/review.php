<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/backend/config/session.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';
require_once dirname(__DIR__, 2) . '/backend/config/auth.php';

requireRole(['moderator']);

$userId = (int) $_SESSION['user_id'];

// Redirect back to the reports page with a result message.
function redirectToReports(string $result, string $statusFilter = 'all'): never
{
    header(
        'Location: ../../pages/moderator/reports.php?status='
        . urlencode($statusFilter)
        . '&result='
        . urlencode($result)
    );
    exit;
}

// Only allow POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectToReports('invalid_request');
}

// Validate CSRF token.
$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    $sessionToken === '' ||
    !hash_equals($sessionToken, $submittedToken)
) {
    redirectToReports('invalid_token');
}

// Get and validate submitted values.
$reportId = filter_input(INPUT_POST, 'report_id', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';
$contentAction = $_POST['content_action'] ?? 'none';
$resolutionNote = trim((string) ($_POST['resolution_note'] ?? ''));

if (!$reportId || $reportId < 1) {
    redirectToReports('invalid_report');
}

$allowedActions = ['start_review', 'resolve', 'dismiss'];

if (!in_array($action, $allowedActions, true)) {
    redirectToReports('invalid_action');
}

if (in_array($action, ['resolve', 'dismiss'], true)) {
    if ($resolutionNote === '') {
        redirectToReports('note_required', 'reviewing');
    }

    if (mb_strlen($resolutionNote) > 2000) {
        redirectToReports('note_too_long', 'reviewing');
    }
}

// Confirm that the moderator account is active.
$accountQuery = $pdo->prepare(
    'SELECT role, is_active
     FROM users
     WHERE id = :id
     LIMIT 1'
);

$accountQuery->execute(['id' => $userId]);
$account = $accountQuery->fetch(PDO::FETCH_ASSOC);

if (
    !$account ||
    $account['role'] !== 'moderator' ||
    (int) $account['is_active'] !== 1
) {
    http_response_code(403);
    exit('Access denied.');
}

try {
    $pdo->beginTransaction();

    // Lock the report and read its target details.
    $reportQuery = $pdo->prepare(
        'SELECT id, target_type, article_id, comment_id, status
         FROM content_reports
         WHERE id = :id
         FOR UPDATE'
    );

    $reportQuery->execute(['id' => $reportId]);
    $report = $reportQuery->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        $pdo->rollBack();
        redirectToReports('report_not_found');
    }

    $currentStatus = $report['status'];
    $targetType = $report['target_type'];
    $articleId = $report['article_id'] !== null ? (int) $report['article_id'] : null;
    $commentId = $report['comment_id'] !== null ? (int) $report['comment_id'] : null;

    if ($action === 'start_review') {

        if ($currentStatus !== 'pending') {
            $pdo->rollBack();
            redirectToReports('invalid_status');
        }

        $updateQuery = $pdo->prepare(
            "UPDATE content_reports
             SET status = 'reviewing',
                 reviewed_by = :reviewed_by,
                 reviewed_at = NOW(),
                 resolution_note = NULL
             WHERE id = :id"
        );

        $updateQuery->execute([
            'reviewed_by' => $userId,
            'id' => $reportId
        ]);

        // Log audit entry.
        $logQuery = $pdo->prepare(
            'INSERT INTO moderation_logs (moderator_id, action, article_id, comment_id, report_id, reason)
             VALUES (:moderator_id, :action, :article_id, :comment_id, :report_id, :reason)'
        );
        $logQuery->execute([
            'moderator_id' => $userId,
            'action'       => 'report_review_started',
            'article_id'   => $articleId,
            'comment_id'   => $commentId,
            'report_id'    => $reportId,
            'reason'       => null
        ]);

        $pdo->commit();
        redirectToReports('review_started', 'reviewing');

    } elseif ($action === 'resolve') {

        if ($currentStatus !== 'reviewing') {
            $pdo->rollBack();
            redirectToReports('invalid_status');
        }

        // Validate and apply content enforcement action.
        if ($targetType === 'article') {
            $allowedArticleActions = ['archive', 'none'];
            if (!in_array($contentAction, $allowedArticleActions, true)) {
                $pdo->rollBack();
                redirectToReports('invalid_action', 'reviewing');
            }

            if ($contentAction === 'archive' && $articleId !== null) {
                $articleStmt = $pdo->prepare(
                    "UPDATE articles
                     SET status = 'archived',
                         reviewed_by = :reviewed_by,
                         reviewed_at = NOW()
                     WHERE id = :article_id"
                );
                $articleStmt->execute([
                    'reviewed_by' => $userId,
                    'article_id'  => $articleId
                ]);
            }
        } elseif ($targetType === 'comment') {
            $allowedCommentActions = ['hide', 'remove', 'none'];
            if (!in_array($contentAction, $allowedCommentActions, true)) {
                $pdo->rollBack();
                redirectToReports('invalid_action', 'reviewing');
            }

            if (in_array($contentAction, ['hide', 'remove'], true) && $commentId !== null) {
                $newCommentStatus = $contentAction === 'hide' ? 'hidden' : 'removed';
                $commentStmt = $pdo->prepare(
                    'UPDATE comments
                     SET status = :status,
                         moderated_by = :moderated_by,
                         moderated_at = NOW()
                     WHERE id = :comment_id'
                );
                $commentStmt->execute([
                    'status'       => $newCommentStatus,
                    'moderated_by' => $userId,
                    'comment_id'   => $commentId
                ]);
            }
        }

        // Mark report as resolved.
        $updateQuery = $pdo->prepare(
            "UPDATE content_reports
             SET status = 'resolved',
                 reviewed_by = :reviewed_by,
                 reviewed_at = NOW(),
                 resolution_note = :resolution_note
             WHERE id = :id"
        );

        $updateQuery->execute([
            'reviewed_by'     => $userId,
            'resolution_note' => $resolutionNote,
            'id'              => $reportId
        ]);

        // Log audit entry.
        $logAction = 'report_resolved_' . $contentAction;
        $logQuery = $pdo->prepare(
            'INSERT INTO moderation_logs (moderator_id, action, article_id, comment_id, report_id, reason)
             VALUES (:moderator_id, :action, :article_id, :comment_id, :report_id, :reason)'
        );
        $logQuery->execute([
            'moderator_id' => $userId,
            'action'       => $logAction,
            'article_id'   => $articleId,
            'comment_id'   => $commentId,
            'report_id'    => $reportId,
            'reason'       => $resolutionNote
        ]);

        $pdo->commit();
        redirectToReports('report_resolved', 'resolved');

    } else {

        if ($currentStatus !== 'reviewing') {
            $pdo->rollBack();
            redirectToReports('invalid_status');
        }

        $updateQuery = $pdo->prepare(
            "UPDATE content_reports
             SET status = 'dismissed',
                 reviewed_by = :reviewed_by,
                 reviewed_at = NOW(),
                 resolution_note = :resolution_note
             WHERE id = :id"
        );

        $updateQuery->execute([
            'reviewed_by'     => $userId,
            'resolution_note' => $resolutionNote,
            'id'              => $reportId
        ]);

        // Log audit entry.
        $logQuery = $pdo->prepare(
            'INSERT INTO moderation_logs (moderator_id, action, article_id, comment_id, report_id, reason)
             VALUES (:moderator_id, :action, :article_id, :comment_id, :report_id, :reason)'
        );
        $logQuery->execute([
            'moderator_id' => $userId,
            'action'       => 'report_dismissed',
            'article_id'   => $articleId,
            'comment_id'   => $commentId,
            'report_id'    => $reportId,
            'reason'       => $resolutionNote
        ]);

        $pdo->commit();
        redirectToReports('report_dismissed', 'dismissed');
    }

} catch (Throwable $exception) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Report moderation error: ' . $exception->getMessage());

    redirectToReports('database_error');
}