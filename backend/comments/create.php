
<?php

require_once dirname(__DIR__, 2) . '/backend/config/session.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
    http_response_code(403);
    exit('Access denied.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Invalid request.');
}

$token = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['comment_csrf']) ||
    !hash_equals($_SESSION['comment_csrf'], $token)
) {
    http_response_code(403);
    exit('Invalid security token.');
}

$articleId = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);
$content = trim($_POST['content'] ?? '');

if (!$articleId || $articleId < 1 || $content === '' || strlen($content) > 2000) {
    header('Location: ../../pages/article.php?id=' . (int) $articleId . '&comment_error=1');
    exit;
}

try {
    $check = $pdo->prepare(
        "SELECT id FROM articles
         WHERE id = ? AND status = 'published'"
    );
    $check->execute([$articleId]);

    if (!$check->fetch()) {
        http_response_code(404);
        exit('Article not found.');
    }

    $stmt = $pdo->prepare(
        "INSERT INTO comments (article_id, user_id, content, status)
         VALUES (?, ?, ?, 'pending')"
    );

    $stmt->execute([
        $articleId,
        $_SESSION['user_id'],
        $content
    ]);

    header('Location: ../../pages/article.php?id=' . $articleId . '&comment_success=1');
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    exit('Unable to submit comment.');
}
