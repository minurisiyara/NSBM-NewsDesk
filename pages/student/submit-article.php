<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/backend/config/session.php';
require_once dirname(__DIR__, 2) . '/backend/config/auth.php';
require_once dirname(__DIR__, 2) . '/backend/config/database.php';

// Only logged-in students can access this page.
requireRole(['student']);

// Check that the account is still active.
$userId = (int) $_SESSION['user_id'];

$accountQuery = $pdo->prepare(
    'SELECT is_active FROM users WHERE id = :user_id LIMIT 1'
);
$accountQuery->execute(['user_id' => $userId]);
$account = $accountQuery->fetch();

if (!$account || (int) $account['is_active'] !== 1) {
    $_SESSION = [];
    session_destroy();

    header('Location: ../auth/login.php');
    exit;
}

// Create a CSRF token for the submission form.
if (
    !isset($_SESSION['csrf_token']) ||
    !is_string($_SESSION['csrf_token']) ||
    $_SESSION['csrf_token'] === ''
) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Load available categories.
$categoryQuery = $pdo->query(
    'SELECT id, name FROM categories ORDER BY name ASC'
);
$categories = $categoryQuery->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Article | NSBM NewsDesk</title>
    <link rel="stylesheet" href="../../assets/css/student.css">
    <style>
        .submission-container {
            max-width: 760px;
            margin: 40px auto;
            padding: 24px;
        }

        .submission-form {
            display: grid;
            gap: 18px;
        }

        .submission-form label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
        }

        .submission-form input,
        .submission-form select,
        .submission-form textarea {
            box-sizing: border-box;
            width: 100%;
            padding: 12px;
            font: inherit;
        }

        .submission-form textarea {
            min-height: 240px;
            resize: vertical;
        }

        .submission-form button {
            padding: 12px 20px;
            cursor: pointer;
        }

        .form-note {
            color: #555;
            line-height: 1.5;
        }
    </style>
</head>
<body>

<main class="submission-container">
    <h1>Submit an Article</h1>

    <p class="form-note">
        Submit your article for moderator review. It will not appear
        in public news until it has been published.
    </p>

    <form
        class="submission-form"
        action="../../backend/news/create.php"
        method="POST"
    >
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(
                $_SESSION['csrf_token'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

        <div>
            <label for="title">Article Title</label>
            <input
                type="text"
                id="title"
                name="title"
                maxlength="255"
                required
            >
        </div>

        <div>
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select a category</option>

                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>">
                        <?= htmlspecialchars(
                            $category['name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="content">Article Content</label>
            <textarea
                id="content"
                name="content"
                required
            ></textarea>
        </div>

        <button type="submit">Submit for Review</button>
    </form>
</main>

</body>
</html>
