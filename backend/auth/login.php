<?php

declare(strict_types=1);

// Include configuration files
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/config/database.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: ../../pages/auth/login.php');
    exit;

}

// Get form data
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate CSRF token
$sessionToken = $_SESSION['csrf_token'] ?? '';
$postedToken = $_POST['csrf_token'] ?? '';

if (
    empty($sessionToken) ||
    empty($postedToken) ||
    !hash_equals($sessionToken, $postedToken)
) {

    header('Location: ../../pages/auth/login.php?error=1');
    exit;

}

// Validate email and password
if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $password === ''
) {

    header('Location: ../../pages/auth/login.php?error=1');
    exit;

}

try {

    // Find user from database
    $sql = "SELECT id, name, email, password, role, is_active
            FROM users
            WHERE email = :email
            LIMIT 1";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':email' => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check user details
    if (
        $user &&
        password_verify($password, $user['password']) &&
        (int)$user['is_active'] === 1 &&
        $user['role'] === 'student'
    ) {

        // Regenerate session ID
        session_regenerate_id(true);

        // Store user details in session
        $_SESSION['user_id'] = $user['id'];

        $_SESSION['user_name'] = $user['name'];

        $_SESSION['user_email'] = $user['email'];

        $_SESSION['user_role'] = $user['role'];

        // Redirect to Student Dashboard
        header('Location: ../../pages/student/dashboard.php');
        exit;

    } else {

        // Login failed
        header('Location: ../../pages/auth/login.php?error=1');
        exit;

    }

} catch (PDOException $e) {

    // Save error to PHP error log
    error_log($e->getMessage());

    // Redirect back to login page
    header('Location: ../../pages/auth/login.php?error=1');
    exit;

}
