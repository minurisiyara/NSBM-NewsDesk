
<?php

require_once dirname(__DIR__, 2) . '/backend/config/session.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Only students can access
if (($_SESSION['user_role'] ?? '') !== 'student') {
    http_response_code(403);
    exit('Access denied.');
}

$userId = (int) $_SESSION['user_id'];
$name = $_SESSION['user_name'] ?? 'Student';

// Create CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$messageType = '';

// Handle subscribe / unsubscribe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';
    $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $message = 'Invalid request. Please try again.';
        $messageType = 'error';

    } elseif (!$categoryId || !in_array($action, ['subscribe', 'unsubscribe'], true)) {
        $message = 'Invalid category or action.';
        $messageType = 'error';

    } else {

        // Check category exists
        $check = $pdo->prepare(
            "SELECT id FROM categories WHERE id = :id"
        );
        $check->execute(['id' => $categoryId]);

        if (!$check->fetch()) {
            $message = 'Category not found.';
            $messageType = 'error';

        } elseif ($action === 'subscribe') {

            // Add subscription only if it does not already exist
            $sql = "
                INSERT INTO subscriptions (user_id, category_id)
                SELECT :user_id, c.id
                FROM categories c
                WHERE c.id = :category_id
                AND NOT EXISTS (
                    SELECT 1
                    FROM subscriptions s
                    WHERE s.user_id = :check_user
                    AND s.category_id = :check_category
                )
            ";


            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'category_id' => $categoryId,
                'check_user' => $userId,
                'check_category' => $categoryId
            ]);
            

            $message = $stmt->rowCount() > 0
                ? 'Successfully subscribed!'
                : 'You are already subscribed to this category.';

            $messageType = 'success';

        } else {

            // Remove subscription
            $stmt = $pdo->prepare("
                DELETE FROM subscriptions
                WHERE user_id = :user_id
                AND category_id = :category_id
            ");

            $stmt->execute([
                'user_id' => $userId,
                'category_id' => $categoryId
            ]);

            $message = $stmt->rowCount() > 0
                ? 'Successfully unsubscribed!'
                : 'You are not subscribed to this category.';

            $messageType = 'success';
        }
    }
}

// Get all categories and subscription status
$sql = "
    SELECT
        c.id,
        c.name,
        CASE
            WHEN s.id IS NULL THEN 0
            ELSE 1
        END AS is_subscribed
    FROM categories c
    LEFT JOIN subscriptions s
        ON c.id = s.category_id
        AND s.user_id = :user_id
    ORDER BY c.name ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $userId]);

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Subscriptions</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: auto;
        }

        h1 {
            color: #243b64;
            margin-bottom: 10px;
        }

        .welcome {
            color: #666;
            margin-bottom: 25px;
        }

        .category-card {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .category-card h3 {
            margin: 0;
            color: #243b64;
        }

        .category-card p {
            margin: 6px 0 0;
            color: #777;
            font-size: 14px;
        }

        button {
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            font-weight: bold;
        }

        .subscribe-btn {
            background: #2563eb;
        }

        .unsubscribe-btn {
            background: #dc3545;
        }

        button:hover {
            opacity: 0.85;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .empty {
            background: white;
            padding: 35px;
            text-align: center;
            border-radius: 10px;
            color: #777;
        }

        @media (max-width: 600px) {
            body {
                padding: 15px;
            }

            .category-card {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h1>My Subscriptions</h1>

    <p class="welcome">
        Welcome, <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>!
        Manage your favourite news categories here.
    </p>

    <?php if ($message !== ''): ?>
        <div class="message <?= $messageType ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($categories)): ?>

        <?php foreach ($categories as $category): ?>

            <div class="category-card">

                <div>
                    <h3>
                        <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                    </h3>

                    <p>
                        <?= $category['is_subscribed']
                            ? 'You are subscribed to this category.'
                            : 'Subscribe to receive updates from this category.' ?>
                    </p>
                </div>

                <form method="POST" action="subscriptions.php">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
                    >

                    <input
                        type="hidden"
                        name="category_id"
                        value="<?= (int) $category['id'] ?>"
                    >

                    <?php if ($category['is_subscribed']): ?>

                        <button
                            type="submit"
                            name="action"
                            value="unsubscribe"
                            class="unsubscribe-btn"
                        >
                            Unsubscribe
                        </button>

                    <?php else: ?>

                        <button
                            type="submit"
                            name="action"
                            value="subscribe"
                            class="subscribe-btn"
                        >
                            Subscribe
                        </button>

                    <?php endif; ?>

                </form>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="empty">
            <h3>No Categories Found!</h3>
            <p>There are no news categories available yet.</p>
        </div>

    <?php endif; ?>

</div>

</body>
</html>