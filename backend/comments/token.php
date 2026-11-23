
<?php

require_once dirname(__DIR__, 2) . '/backend/config/session.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied.'
    ]);
    exit;
}

if (empty($_SESSION['comment_csrf'])) {
    $_SESSION['comment_csrf'] = bin2hex(random_bytes(32));
}

echo json_encode([
    'success' => true,
    'csrf_token' => $_SESSION['comment_csrf']
]);
