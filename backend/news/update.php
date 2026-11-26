<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed.');
}

// Require a logged-in user.
requireLogin();

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

// Confirm the account is active and its role is current.
$userId = (int) $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];

$accountQuery = $pdo->prepare(
    'SELECT role, is_active FROM users WHERE id = :id LIMIT 1'
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

// Validate article ID.
$articleId = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);

if ($articleId === false || $articleId === null || $articleId < 1) {
    http_response_code(422);
    exit('A valid article ID is required.');
}

// Load the article.
$articleQuery = $pdo->prepare(
    'SELECT id, title, content, author_id, category_id, status
     FROM articles
     WHERE id = :id
     LIMIT 1'
);
$articleQuery->execute(['id' => $articleId]);
$article = $articleQuery->fetch();

if (!$article) {
    http_response_code(404);
    exit('Article not found.');
}

/*
 * STUDENT: edit their own draft or pending article.
 * Editing a pending article sends it back to pending.
 */
if ($userRole === 'student') {
    if ((int) $article['author_id'] !== $userId) {
        http_response_code(403);
        exit('You can only edit your own articles.');
    }

    if (!in_array($article['status'], ['draft', 'pending'], true)) {
        http_response_code(403);
        exit('This article can no longer be edited.');
    }

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $categoryId = filter_input(
        INPUT_POST,
        'category_id',
        FILTER_VALIDATE_INT
    );

    if ($title === '' || mb_strlen($title) > 255) {
        http_response_code(422);
        exit('Title must contain 1–255 characters.');
    }

    if ($content === '') {
        http_response_code(422);
        exit('Article content is required.');
    }

    if (
        $categoryId === false ||
        $categoryId === null ||
        $categoryId < 1
    ) {
        http_response_code(422);
        exit('A valid category is required.');
    }

    $categoryQuery = $pdo->prepare(
        'SELECT id FROM categories WHERE id = :id LIMIT 1'
    );
    $categoryQuery->execute(['id' => $categoryId]);

    if (!$categoryQuery->fetch()) {
        http_response_code(422);
        exit('The selected category does not exist.');
    }

    $updateQuery = $pdo->prepare(
        "UPDATE articles
         SET title = :title,
             content = :content,
             category_id = :category_id,
             status = 'pending',
             rejection_reason = NULL,
             reviewed_by = NULL,
             reviewed_at = NULL
         WHERE id = :id AND author_id = :author_id"
    );

    $updateQuery->execute([
        'title' => $title,
        'content' => $content,
        'category_id' => $categoryId,
        'id' => $articleId,
        'author_id' => $userId,
    ]);

    echo 'Article updated and submitted for moderation.';
    exit;
}

/*
 * MODERATOR: approve or reject a pending article.
 */

if ($userRole === 'moderator') {
    if ((int) $article['author_id'] === $userId) {
        header('Location: ../pages/moderator/approvals.php?error=' . rawurlencode('You cannot review your own article.'));
        exit;
    }

    if ($article['status'] !== 'pending') {
        header('Location: ../pages/moderator/approvals.php?error=' . rawurlencode('Only pending articles can be reviewed.'));
        exit;
    }

    $action = $_POST['action'] ?? '';

    if (!is_string($action) || !in_array($action, ['approve', 'reject'], true)) {
        header('Location: ../pages/moderator/approvals.php?error=' . rawurlencode('Choose approve or reject.'));
        exit;
    }

    $rejectionReason = trim($_POST['rejection_reason'] ?? '');

    if (mb_strlen($rejectionReason) > 2000) {
        header('Location: ../pages/moderator/approvals.php?error=' . rawurlencode('Rejection reason must not exceed 2000 characters.'));
        exit;
    }

    if ($action === 'reject' && $rejectionReason === '') {
        header('Location: ../pages/moderator/approvals.php?error=' . rawurlencode('A rejection reason is required.'));
        exit;
    }

    try {
        $pdo->beginTransaction();

        if ($action === 'approve') {
            $updateQuery = $pdo->prepare(
                "UPDATE articles
                 SET status = 'approved',
                     rejection_reason = NULL,
                     reviewed_by = :reviewer,
                     reviewed_at = CURRENT_TIMESTAMP
                 WHERE id = :id AND status = 'pending'"
            );

            $updateQuery->execute([
                'reviewer' => $userId,
                'id' => $articleId,
            ]);

            $logAction = 'article_approved';
            $logReason = null;
            $successMsg = 'Article approved successfully.';
        } else {
            $updateQuery = $pdo->prepare(
                "UPDATE articles
                 SET status = 'rejected',
                     rejection_reason = :reason,
                     reviewed_by = :reviewer,
                     reviewed_at = CURRENT_TIMESTAMP
                 WHERE id = :id AND status = 'pending'"
            );

            $updateQuery->execute([
                'reason' => $rejectionReason,
                'reviewer' => $userId,
                'id' => $articleId,
            ]);

            $logAction = 'article_rejected';
            $logReason = $rejectionReason;
            $successMsg = 'Article rejected and feedback recorded.';
        }

        if ($updateQuery->rowCount() !== 1) {
            $pdo->rollBack();
            header('Location: ../pages/moderator/approvals.php?error=' . rawurlencode('The article could not be updated. Refresh and try again.'));
            exit;
        }

        // Record audit log entry.
        $logQuery = $pdo->prepare(
            'INSERT INTO moderation_logs (moderator_id, action, article_id, reason)
             VALUES (:moderator_id, :action, :article_id, :reason)'
        );
        $logQuery->execute([
            'moderator_id' => $userId,
            'action'       => $logAction,
            'article_id'   => $articleId,
            'reason'       => $logReason,
        ]);

        $pdo->commit();

        header('Location: ../pages/moderator/approvals.php?success=' . rawurlencode($successMsg));
        exit;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Article moderation failed: ' . $e->getMessage());
        header('Location: ../pages/moderator/approvals.php?error=' . rawurlencode('Unable to process article review. Please try again.'));
        exit;
    }
}

// Admin updates and publishing require a separate, explicit workflow.
http_response_code(403);
exit('This role cannot perform this update.');