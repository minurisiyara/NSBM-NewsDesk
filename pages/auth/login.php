<?php
require_once dirname(__DIR__, 2) . '/backend/config/session.php';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = $_SESSION['login_errors'] ?? [];
unset($_SESSION['login_errors']);

$success = $_SESSION['register_success'] ?? '';
unset($_SESSION['register_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | NSBM NewsDesk</title>
    <link rel="stylesheet" href="../../assets/css/auth.css">
</head>
<body>

    <main class="auth-container">
        <h1>NSBM NewsDesk</h1>
        <h2>Welcome Back</h2>
        <p>Log in to your account.</p>

        <?php if ($success !== ''): ?>
            <div class="success-message">
                <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="../../backend/auth/login.php"
                 method="POST"
                     id="loginForm">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
            >
            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" id="loginButton">
                    Log In
            </button>
        </form>

        <p>
            Don't have an account?
            <a href="register.php">Create an account</a>
        </p>
    </main>
    <script src="../../assets/js/auth.js"></script>

</body>
</html>
