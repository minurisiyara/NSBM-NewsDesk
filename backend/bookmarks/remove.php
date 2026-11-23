<?php

require_once dirname(__DIR__, 2) . '/backend/config/session.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access.'
    ]);
    exit;
}

if (empty($_SESSION['bookmark_csrf'])) {
    $_SESSION['bookmark_csrf'] = bin2hex(random_bytes(32));
}

$data = json_decode(file_get_contents('php://input'), true);

if (!hash_equals($_SESSION['bookmark_csrf'], $data['csrf'] ?? '')) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid security token.'
    ]);
    exit;
}

$bookmarkId = filter_var(
    $data['bookmark_id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$bookmarkId || $bookmarkId < 1) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid bookmark.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "DELETE FROM bookmarks
         WHERE id = ? AND user_id = ?"
    );

    $stmt->execute([
        $bookmarkId,
        $_SESSION['user_id']
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Bookmark not found.'
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error.'
    ]);
}