<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/session.php';

// Identify the current user, if logged in.
$userId = isset($_SESSION['user_id'])
    ? (int) $_SESSION['user_id']
    : null;

$userRole = $_SESSION['user_role'] ?? null;

// Check whether a logged-in account is still active.
if ($userId !== null) {
    $accountQuery = $pdo->prepare(
        'SELECT role, is_active
         FROM users
         WHERE id = :user_id
         LIMIT 1'
    );
    $accountQuery->execute(['user_id' => $userId]);
    $account = $accountQuery->fetch();

    if (
        !$account ||
        (int) $account['is_active'] !== 1 ||
        $account['role'] !== $userRole
    ) {
        http_response_code(403);
        exit('Your account is inactive or unavailable.');
    }
}

// Build the query according to the user's role.
if ($userRole === 'student' && $userId !== null) {
    // Students can retrieve only their own submissions.
    $query = $pdo->prepare(
        'SELECT id, title, content, category_id, status,
                rejection_reason, created_at, updated_at
         FROM articles
         WHERE author_id = :author_id
         ORDER BY created_at DESC'
    );

    $query->execute(['author_id' => $userId]);

} elseif ($userRole === 'moderator' && $userId !== null) {
    // Moderators can retrieve articles awaiting review.
    $query = $pdo->prepare(
        "SELECT id, title, content, author_id, category_id,
                status, created_at
         FROM articles
         WHERE status = 'pending'
         ORDER BY created_at ASC"
    );

    $query->execute();

} elseif ($userRole === 'admin' && $userId !== null) {
    // Admins can retrieve articles for administrative purposes.
    $query = $pdo->prepare(
        'SELECT id, title, content, author_id, category_id,
                status, rejection_reason, reviewed_by,
                reviewed_at, is_featured, is_breaking,
                image_path, published_at, created_at, updated_at
         FROM articles
         ORDER BY created_at DESC'
    );

    $query->execute();

} else {
    // Public visitors can retrieve published articles only.
    $query = $pdo->prepare(
        "SELECT id, title, content, category_id, image_path,
                published_at
         FROM articles
         WHERE status = 'published'
         ORDER BY published_at DESC"
    );

    $query->execute();
}

$articles = $query->fetchAll();

// Render a simple HTML page.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles | NSBM NewsDesk</title>
</head>
<body>

<main>
    <h1>Articles</h1>

    <?php if (!$articles): ?>
        <p>No articles found.</p>
    <?php else: ?>
        <?php foreach ($articles as $article): ?>
            <article>
                <h2>
                    <?= htmlspecialchars(
                        $article['title'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </h2>

                <?php if (isset($article['status'])): ?>
                    <p>
                        Status:
                        <?= htmlspecialchars(
                            $article['status'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>
                <?php endif; ?>

                <p>
                    <?= nl2br(htmlspecialchars(
                        $article['content'],
                        ENT_QUOTES,
                        'UTF-8'
                    )) ?>
                </p>
            </article>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

</body>
</html>
