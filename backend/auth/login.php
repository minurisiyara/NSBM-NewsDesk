<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/session.php';

require_once dirname(__DIR__) . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/auth/login.php');
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
    exit('Invalid or expired form token. Please reload the login page and try again.');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    $_SESSION['login_errors'] = [
        'Please enter a valid email address and password.'
    ];

    header('Location: ../../pages/auth/login.php');
    exit;
}

try {
    $stmt = $pdo->prepare(
        'SELECT id, name, email, password, role, is_active
         FROM users
         WHERE email = :email
         LIMIT 1'
    );

    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (
        !$user ||
        !password_verify($password, $user['password']) ||
        (int) $user['is_active'] !== 1
    ) {
        $_SESSION['login_errors'] = [
            'Invalid email or password, or your account is inactive.'
        ];

        header('Location: ../../pages/auth/login.php');
        exit;
    }

    // Do not establish a session for an unavailable role.
    if (!in_array($user['role'], ['student', 'admin'], true)) {
        $_SESSION['login_errors'] = [
            'Access for this account type is not available yet.'
        ];

        header('Location: ../../pages/auth/login.php');
        exit;
    }

    // Regenerate the session ID after successful authentication.
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    if ($user['role'] === 'admin') {
        header('Location: ../../pages/admin/dashboard.php');
    } else {
        header('Location: ../../pages/student/dashboard.php');
    }

    exit;

} catch (PDOException $e) {
    error_log('Login failed: ' . $e->getMessage());

    $_SESSION['login_errors'] = [
        'Unable to log in right now. Please try again.'
    ];

    header('Location: ../../pages/auth/login.php');
    exit;
}
