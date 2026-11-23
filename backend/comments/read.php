
<?php

require_once dirname(__DIR__, 2) . '/backend/config/session.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied.'
    ]);
    exit;
}

$articleId = filter_input(INPUT_GET, 'article_id', FILTER_VALIDATE_INT);

if (!$articleId || $articleId < 1) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid article ID.'
    ]);
    exit;
}

try {

    $stmt = $pdo->prepare(
        "SELECT
            comments.id,
            comments.user_id,
            comments.content,
            comments.created_at,
            users.name
         FROM comments
         INNER JOIN users ON comments.user_id = users.id
         WHERE comments.article_id = ?
         AND (
             comments.status = 'approved'
             OR (
                 comments.user_id = ?
                 AND comments.status = 'pending'
             )
         )
         ORDER BY comments.created_at DESC"
    );

    $stmt->execute([
        $articleId,
        (int) $_SESSION['user_id']
    ]);

    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $currentUserId = (int) $_SESSION['user_id'];

    foreach ($comments as &$comment) {
        $comment['is_owner'] = (
            (int) $comment['user_id'] === $currentUserId
        );

        unset($comment['user_id']);
    }

    unset($comment);

    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load comments.'
    ]);
}

