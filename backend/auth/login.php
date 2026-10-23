<?php

declare(strict_types=1);

session_start();

require_once dirname(__DIR__) . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/auth/login.php');
    exit;
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

    // Prevent session fixation after successful authentication.
    session_regenerate_id(true);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    // Redirect users according to their role.
    switch ($user['role']) {
        case 'admin':
            header('Location: ../../pages/admin/dashboard.php');
            break;

        case 'moderator':
            $_SESSION['login_errors'] = [
                'Moderator access is not available yet.'
            ];
        
            header('Location: ../../pages/auth/login.php');
            break;

        case 'student':
            header('Location: ../../pages/student/dashboard.php');
            break;

        default:
            session_unset();
            session_destroy();
            header('Location: ../../pages/auth/login.php');
            break;
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
