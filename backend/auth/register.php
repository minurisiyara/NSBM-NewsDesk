<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/session.php';

require_once dirname(__DIR__) . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/auth/register.php');
    exit;
}

$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    $sessionToken === '' ||
    !hash_equals($sessionToken, $submittedToken)
) {
    http_response_code(403);
    exit('Invalid or expired form token. Please return to the registration page and try again.');
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$errors = [];

if ($name === '' || strlen($name) > 100) {
    $errors[] = 'Name is required and must be at most 100 bytes.';
}

if (
    strlen($email) > 254 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    $errors[] = 'Enter a valid email address.';
}

if (strlen($password) < 8 || strlen($password) > 72) {
    $errors[] = 'Password must be between 8 and 72 bytes.';
}

if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match.';
}

if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    header('Location: ../../pages/auth/register.php');
    exit;
}

try {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, password, role)
         VALUES (:name, :email, :password, :role)'
    );

    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword,
        'role' => 'student'
    ]);

    $_SESSION['register_success'] =
        'Registration successful. Please log in.';

    header('Location: ../../pages/auth/login.php');
    exit;

} catch (PDOException $e) {
    error_log('Registration failed: ' . $e->getMessage());

    if ($e->getCode() === '23000') {
        $_SESSION['register_errors'] = [
            'This email address may already be registered.'
        ];
    } else {
        $_SESSION['register_errors'] = [
            'Registration failed. Please try again.'
        ];
    }

    header('Location: ../../pages/auth/register.php');
    exit;
}
