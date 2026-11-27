<?php
require_once '../../backend/config/session.php';
require_once '../../backend/config/database.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Check student role
if (($_SESSION['user_role'] ?? '') !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Get subscribed categories
$stmt = $pdo->prepare("
    SELECT 
        c.id,
        c.name,
        c.description,
        s.created_at
    FROM subscriptions s
    INNER JOIN categories c ON s.category_id = c.id
    WHERE s.user_id = ?
    ORDER BY s.created_at DESC
");

$stmt->execute([$userId]);
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Subscriptions | NSBM NewsDesk</title>

    <link rel="stylesheet" href="../../assets/css/student.css">
    <link rel="stylesheet" href="../../assets/css/subscriptions.css">
</head>

<body>

<div class="dashboard-container">

    <!-- Sidebar -->
    <aside class="sidebar">

        <div class="brand">
            <div class="brand-icon">N</div>
            <h2>NewsDesk</h2>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="submit-article.php">Submit Article</a>
            <a href="my-submissions.php">My Submissions</a>
            <a href="bookmarks.php">Bookmarks</a>
            <a href="subscriptions.php" class="active">Subscriptions</a>
            <a href="profile.php">My Profile</a>
        </nav>

        <div class="sidebar-bottom">
            <a href="../../backend/auth/logout.php">Logout</a>
        </div>

    </aside>

    <!-- Main Content -->
    <main class="main-content">

        <header class="topbar">
            <h2>My Subscriptions</h2>
        </header>

        <section class="subscriptions-content">

            <div class="subscriptions-heading">
                <h2>Subscribed Categories</h2>
                <p>Categories you have subscribed to.</p>
            </div>

            <?php if (count($subscriptions) > 0): ?>

                <div class="subscriptions-grid">

                    <?php foreach ($subscriptions as $category): ?>

                        <div class="subscription-card">

                            <div class="subscription-icon">
                                📁
                            </div>

                            <div class="subscription-details">

                                <h3>
                                    <?= htmlspecialchars($category['name']) ?>
                                </h3>

                                <p>
                                    <?= htmlspecialchars(
                                        $category['description'] ?? 'No description available.'
                                    ) ?>
                                </p>

                                <small>
                                    Subscribed on:
                                    <?= date(
                                        'M d, Y',
                                        strtotime($category['created_at'])
                                    ) ?>
                                </small>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="subscription-empty">

                    <h3>No Subscriptions Yet</h3>

                    <p>
                        You have not subscribed to any categories yet.
                    </p>

                </div>

            <?php endif; ?>

        </section>

    </main>

</div>

</body>
</html>