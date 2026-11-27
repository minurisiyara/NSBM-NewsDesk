
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

    // Verify user credentials and account status
    if (
        !$user ||
        !password_verify($password, $user['password']) ||
        (int) $user['is_active'] !== 1
    ) {
        header('Location: ../../pages/auth/login.php?error=1');
        exit;
    }

    // Only allow supported user roles
    if (!in_array($user['role'], ['student', 'moderator', 'admin'], true)) {
        $_SESSION['login_errors'] = [
            'Access for this account type is not available yet.'
        ];

        header('Location: ../../pages/auth/login.php');
        exit;
    }

    // Regenerate session ID after successful authentication
    session_regenerate_id(true);

    // Store user details in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    // Redirect according to user role
    if ($user['role'] === 'admin') {
        header('Location: ../../pages/admin/dashboard.php');
    } elseif ($user['role'] === 'moderator') {
        header('Location: ../../pages/moderator/approvals.php');
    } else {
        header('Location: ../../pages/student/dashboard.php');
    }

    exit;

} catch (PDOException $e) {
    // Save error to PHP error log
    error_log($e->getMessage());

    // Redirect back to login page
    header('Location: ../../pages/auth/login.php?error=1');
    exit;
}
