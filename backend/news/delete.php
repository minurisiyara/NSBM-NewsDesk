<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed.');
}

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

// Verify the current account and role.
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

$articleId = filter_input(
    INPUT_POST,
    'article_id',
    FILTER_VALIDATE_INT
);

if ($articleId === false || $articleId === null || $articleId < 1) {
    http_response_code(422);
    exit('A valid article ID is required.');
}

$articleQuery = $pdo->prepare(
    'SELECT id, author_id, status
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

// Students may permanently delete their own drafts only.
if ($userRole === 'student') {
    if ((int) $article['author_id'] !== $userId) {
        http_response_code(403);
        exit('You can only delete your own articles.');
    }

    if ($article['status'] !== 'draft') {
        http_response_code(403);
        exit('Only draft articles can be deleted by students.');
    }

} elseif ($userRole === 'admin') {
    // Admins archive instead of permanently deleting articles.
    if ($article['status'] === 'archived') {
        http_response_code(409);
        exit('This article is already archived.');
    }

    $archiveQuery = $pdo->prepare(
        "UPDATE articles
         SET status = 'archived'
         WHERE id = :id"
    );
    $archiveQuery->execute(['id' => $articleId]);

    echo 'Article archived successfully.';
    exit;

} else {
    http_response_code(403);
    exit('You do not have permission to delete or archive articles.');
}

// Permanently delete an eligible student draft.
$deleteQuery = $pdo->prepare(
    "DELETE FROM articles
     WHERE id = :id
       AND author_id = :author_id
       AND status = 'draft'"
);
$deleteQuery->execute([
    'id' => $articleId,
    'author_id' => $userId,
]);

if ($deleteQuery->rowCount() !== 1) {
    http_response_code(409);
    exit('The draft could not be deleted. Refresh and try again.');
}

echo 'Draft deleted successfully.';
