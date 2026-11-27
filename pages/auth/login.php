<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/backend/config/session.php';

// Create CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// If already logged in, go to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ../student/dashboard.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | NSBM NewsDesk</title>

    <link rel="stylesheet" href="../../assets/css/auth.css">

</head>

<body class="login-page">

    <div class="auth-container">

        <div class="auth-card">

            <!-- Logo -->
            <div class="auth-logo">

                <img
                    src="../../assets/images/nsbm-logo.png"
                    alt="NSBM Logo"
                    class="login-logo"
                >

                <h2>NSBM NewsDesk</h2>

            </div>

            <!-- Heading -->
            <div class="auth-heading">

                <h1>Welcome Back</h1>

                <p>Log in to your account.</p>

            </div>

            <!-- Error Message -->
            <?php if (isset($_GET['error'])): ?>

                <div class="error-message">

                    Invalid email or password.
                    Please try again.

                </div>

            <?php endif; ?>

            <!-- Login Form -->
            <form
                id="loginForm"
                action="../../backend/auth/login.php"
                method="POST"
            >

                <!-- CSRF Token -->
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        $_SESSION['csrf_token'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                <!-- Email -->
                <div class="form-group">

                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email address"
                        autocomplete="email"
                        required
                    >

                </div>

                <!-- Password -->
                <div class="form-group">

                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                </div>

                <!-- Login Button -->
                <button
                    type="submit"
                    id="loginButton"
                    class="auth-button"
                >
                    Log In
                </button>

            </form>

            <!-- Register Link -->
            <div class="auth-footer">

                <p>
                    Don't have an account?

                    <a href="register.php">
                        Create an account
                    </a>
                </p>

            </div>

        </div>

    </div>

    <script src="../../assets/js/auth.js"></script>

</body>

</html>