<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';

// Allow moderators only.
requireRole(['moderator']);

// Accept POST requests only.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed.');
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
    http_response_code(403);
    exit('Invalid or expired request token.');
}

// Verify the moderator account.
$userId = (int) $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];

$accountQuery = $pdo->prepare(
    'SELECT role, is_active
     FROM users
     WHERE id = :id
     LIMIT 1'
);

$accountQuery->execute(['id' => $userId]);
$account = $accountQuery->fetch();

if (
    !$account ||
    (int) $account['is_active'] !== 1 ||
    $account['role'] !== 'moderator' ||
    $userRole !== 'moderator'
) {
    http_response_code(403);
    exit('Your moderator account is inactive or unavailable.');
}

// Validate comment ID.
$commentId = filter_var(
    $_POST['comment_id'] ?? null,
    FILTER_VALIDATE_INT
);

if ($commentId === false || $commentId === null || $commentId < 1) {
    http_response_code(422);
    exit('A valid comment ID is required.');
}

// Validate requested action.
$action = $_POST['action'] ?? '';

$allowedActions = ['approve', 'hide', 'restore', 'remove'];

if (
    !is_string($action) ||
    !in_array($action, $allowedActions, true)
) {
    http_response_code(422);
    exit('Invalid moderation action.');
}

// Optional moderation reason.
$reason = $_POST['reason'] ?? '';

if (!is_string($reason)) {
    http_response_code(422);
    exit('Invalid moderation reason.');
}

$reason = trim($reason);

if (mb_strlen($reason) > 2000) {
    http_response_code(422);
    exit('The moderation reason must not exceed 2000 characters.');
}

// Define permitted status transitions.
$transitions = [
    'approve' => [
        'from' => ['pending'],
        'to' => 'approved',
        'log' => 'comment_approved',
        'message' => 'Comment approved successfully.',
    ],
    'hide' => [
        'from' => ['pending', 'approved'],
        'to' => 'hidden',
        'log' => 'comment_hidden',
        'message' => 'Comment hidden successfully.',
    ],
    'restore' => [
        'from' => ['hidden'],
        'to' => 'approved',
        'log' => 'comment_restored',
        'message' => 'Comment restored successfully.',
    ],
    'remove' => [
        'from' => ['pending', 'approved', 'hidden'],
        'to' => 'removed',
        'log' => 'comment_removed',
        'message' => 'Comment removed successfully.',
    ],
];

$transition = $transitions[$action];

try {
    $pdo->beginTransaction();

    // Lock the comment to prevent simultaneous moderation.
    $commentQuery = $pdo->prepare(
        'SELECT id, article_id, status
         FROM comments
         WHERE id = :id
         FOR UPDATE'
    );

    $commentQuery->execute(['id' => $commentId]);
    $comment = $commentQuery->fetch();

    if (!$comment) {
        $pdo->rollBack();
        http_response_code(404);
        exit('Comment not found.');
    }

    // Check whether the requested transition is permitted.
    if (!in_array($comment['status'], $transition['from'], true)) {
        $pdo->rollBack();
        http_response_code(409);
        exit('This action is not available for the current comment status.');
    }

    // Update comment status.
    $updateQuery = $pdo->prepare(
        'UPDATE comments
         SET status = :status,
             moderated_by = :moderator_id,
             moderated_at = CURRENT_TIMESTAMP
         WHERE id = :id'
    );

    $updateQuery->execute([
        'status' => $transition['to'],
        'moderator_id' => $userId,
        'id' => $commentId,
    ]);

    if ($updateQuery->rowCount() !== 1) {
        throw new RuntimeException('Comment update failed.');
    }

    // Record the moderation action.
    $logQuery = $pdo->prepare(
        'INSERT INTO moderation_logs
            (moderator_id, action, article_id, comment_id, reason)
         VALUES
            (:moderator_id, :action, :article_id, :comment_id, :reason)'
    );

    $logQuery->execute([
        'moderator_id' => $userId,
        'action' => $transition['log'],
        'article_id' => $comment['article_id'],
        'comment_id' => $commentId,
        'reason' => $reason !== '' ? $reason : null,
    ]);

    // Commit both operations together.
    $pdo->commit();

    header(
        'Location: ../../pages/moderator/comments.php?success=' .
        rawurlencode($transition['message'])
    );
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Comment moderation failed: ' . $e->getMessage());

    http_response_code(500);
    exit('Unable to process the moderation action. Please try again.');
}
