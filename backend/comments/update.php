
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
$content = trim($_POST['content'] ?? '');

if (!$commentId || $commentId < 1 || $content === '' || strlen($content) > 2000) {
    http_response_code(400);
    exit('Invalid comment details.');
}

try {
    $stmt = $pdo->prepare(
        "UPDATE comments
         SET content = ?, status = 'pending'
         WHERE id = ? AND user_id = ?
         AND status IN ('approved', 'pending')"
    );

    $stmt->execute([
        $content,
        $commentId,
        $_SESSION['user_id']
    ]);

    if ($stmt->rowCount() > 0) {
        echo 'Comment updated successfully. It will appear after approval.';
    } else {
        http_response_code(404);
        echo 'Comment not found or cannot be edited.';
    }

} catch (PDOException $e) {
    http_response_code(500);
    exit('Unable to update comment.');
}
