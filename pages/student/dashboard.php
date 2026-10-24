<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/backend/config/session.php';

// Make sure the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Only students can access this page.
if (($_SESSION['user_role'] ?? '') !== 'student') {
    http_response_code(403);
    exit('Access denied.');
}

$name = $_SESSION['user_name'] ?? 'Student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | NSBM NewsDesk</title>
    <link rel="stylesheet" href="../../assets/css/auth.css">
    <style>
        .dashboard-container {
            width: 100%;
            max-width: 800px;
            margin: 40px auto;
            padding: 24px;
        }

        .dashboard-card {
            background: white;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .dashboard-card h2 {
            color: #006747;
            margin-bottom: 12px;
        }

        .dashboard-card p {
            margin-bottom: 16px;
            line-height: 1.6;
        }

        .logout-button {
            background: #006747;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 20px;
            cursor: pointer;
            font-size: 15px;
        }

        .logout-button:hover {
            background: #004d36;
        }
    </style>
</head>
<body>

    <main class="dashboard-container">
        <div class="dashboard-card">
            <h1>NSBM NewsDesk</h1>

            <h2>Welcome, <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>!</h2>

            <p>You have successfully logged in to your student account.</p>

            <p>
                <strong>Account role:</strong>
                Student
            </p>

            <p>
                Your student dashboard is being developed.
                Campus news and other features will be available here later.
            </p>

            <form action="../../backend/auth/logout.php" method="POST">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >

                <button class="logout-button" type="submit">
                    Log Out
                </button>
            </form>
        </div>
    </main>

</body>
</html>
