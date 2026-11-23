
<?php

require_once dirname(__DIR__, 2) . '/backend/config/session.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit;
}

// Generate CSRF token
if (empty($_SESSION['bookmark_csrf'])) {
    $_SESSION['bookmark_csrf'] = bin2hex(random_bytes(32));
}

// Send token to JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['csrf' => $_SESSION['bookmark_csrf']]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!hash_equals($_SESSION['bookmark_csrf'], $data['csrf'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit;
}

$articleId = filter_var($data['article_id'] ?? null, FILTER_VALIDATE_INT);

if (!$articleId || $articleId < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid article.']);
    exit;
}

try {
    $check = $pdo->prepare(
        "SELECT id FROM articles WHERE id = ? AND status = 'published'"
    );
    $check->execute([$articleId]);

    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Article not found.']);
        exit;
    }

    $userId = $_SESSION['user_id'];

    $find = $pdo->prepare(
        "SELECT id FROM bookmarks WHERE user_id = ? AND article_id = ?"
    );
    $find->execute([$userId, $articleId]);

    if ($find->fetch()) {
        $delete = $pdo->prepare(
            "DELETE FROM bookmarks WHERE user_id = ? AND article_id = ?"
        );
        $delete->execute([$userId, $articleId]);

        echo json_encode(['success' => true, 'saved' => false]);
    } else {
        $insert = $pdo->prepare(
            "INSERT INTO bookmarks (user_id, article_id) VALUES (?, ?)"
        );
        $insert->execute([$userId, $articleId]);

        echo json_encode(['success' => true, 'saved' => true]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
