
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

$commentId = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);

if (!$commentId || $commentId < 1) {
    http_response_code(400);
    exit('Invalid comment ID.');
}

try {
    $stmt = $pdo->prepare(
        "DELETE FROM comments
         WHERE id = ? AND user_id = ?"
    );

    $stmt->execute([
        $commentId,
        $_SESSION['user_id']
    ]);

    if ($stmt->rowCount() > 0) {
        echo 'Comment deleted successfully.';
    } else {
        http_response_code(404);
        echo 'Comment not found.';
    }

} catch (PDOException $e) {
    http_response_code(500);
    exit('Unable to delete comment.');
}

