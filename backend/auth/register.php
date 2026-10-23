<?php

declare(strict_types=1);

session_start();

require_once dirname(__DIR__) . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/auth/register.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$errors = [];

if ($name === '') {
    $errors[] = 'Name is required.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Enter a valid email address.';
}

if (strlen($password) < 8) {
    $errors[] = 'Password must contain at least 8 characters.';
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
