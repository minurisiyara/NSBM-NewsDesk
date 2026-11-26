<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';

// Only allow POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed.');
}

// Require a logged-in student.
requireRole(['student']);

/**
 * Validate the CSRF token.
 */
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


// Verify the account is still active.
$userId = (int) $_SESSION['user_id'];


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
    $account['role'] !== $_SESSION['user_role']
) {
    http_response_code(403);
    exit('Your account is inactive or unavailable.');
}


// Read and validate form fields.
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$categoryId = filter_input(
    INPUT_POST,
    'category_id',
    FILTER_VALIDATE_INT
);

if ($title === '' || mb_strlen($title) > 255) {
    http_response_code(422);
    exit('Please provide a title of 1–255 characters.');
}

if ($content === '') {
    http_response_code(422);
    exit('Article content is required.');
}

if ($categoryId === false || $categoryId === null || $categoryId < 1) {
    http_response_code(422);
    exit('Please select a valid category.');
}

// Confirm that the category exists.
$categoryQuery = $pdo->prepare(
    'SELECT id FROM categories WHERE id = :category_id LIMIT 1'
);
$categoryQuery->execute(['category_id' => $categoryId]);

if (!$categoryQuery->fetch()) {
    http_response_code(422);
    exit('The selected category does not exist.');
}

// Save the article as pending moderation.
$insertQuery = $pdo->prepare(
    'INSERT INTO articles (title, content, author_id, category_id, status)
     VALUES (:title, :content, :author_id, :category_id, :status)'
);

$insertQuery->execute([
    'title' => $title,
    'content' => $content,
    'author_id' => $userId,
    'category_id' => $categoryId,
    'status' => 'pending',
]);

http_response_code(201);

echo 'Article submitted successfully. It is awaiting moderation.';
