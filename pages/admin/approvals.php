<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/backend/config/session.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';
require_once dirname(__DIR__, 2) . '/backend/config/auth.php';

requireRole(['moderator']);

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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$query = $pdo->prepare(
    "SELECT a.id, a.title, a.content, a.created_at,
            u.name AS author_name, c.name AS category_name
     FROM articles a
     INNER JOIN users u ON u.id = a.author_id
     INNER JOIN categories c ON c.id = a.category_id
     WHERE a.status = 'pending'
       AND a.author_id != :user_id
     ORDER BY a.created_at ASC"
);

$query->execute(['user_id' => $userId]);
$articles = $query->fetchAll();

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
    <title>Moderate Articles | NSBM NewsDesk</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
            background: #f5f7fb;
            color: #222;
        }
        .article {
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 22px;
            margin: 20px 0;
        }
        .meta { color: #666; font-size: 14px; }
        .content { white-space: pre-wrap; overflow-wrap: anywhere; }
        textarea {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            margin: 10px 0;
        }
        button {
            padding: 10px 18px;
            border: 0;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 8px;
        }
        .approve { background: #16794b; color: white; }
        .reject { background: #b42318; color: white; }
    </style>
</head>
<body>
    <h1>Article Moderation</h1>
    <p>Review pending articles before they move to the next stage.</p>

    <?php if (!$articles): ?>
        <p>There are no pending articles available for review.</p>
    <?php endif; ?>

    <?php foreach ($articles as $article): ?>
        <section class="article">
            <h2><?= e($article['title']) ?></h2>

            <p class="meta">
                Author: <?= e($article['author_name']) ?> |
                Category: <?= e($article['category_name']) ?> |
                Submitted: <?= e($article['created_at']) ?>
            </p>

            <div class="content"><?= e($article['content']) ?></div>

            <form method="post"
                  action="../../backend/news/update.php"
                  onsubmit="return confirm('Submit this moderation decision?');">

                <input type="hidden" name="csrf_token"
                       value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="article_id"
                       value="<?= (int) $article['id'] ?>">

                <label for="reason-<?= (int) $article['id'] ?>">
                    Rejection reason (required when rejecting)
                </label>

                <textarea
                    id="reason-<?= (int) $article['id'] ?>"
                    name="rejection_reason"
                    rows="3"
                    maxlength="2000"
                    placeholder="Explain why this article is rejected."
                ></textarea>

                <button class="approve" type="submit"
                        name="action" value="approve">
                    Approve
                </button>

                <button class="reject" type="submit"
                        name="action" value="reject">
                    Reject
                </button>
            </form>
        </section>
    <?php endforeach; ?>
</body>
</html>
