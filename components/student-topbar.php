<?php

declare(strict_types=1);

// Load session and database
require_once dirname(__DIR__) . '/backend/config/session.php';
require_once dirname(__DIR__) . '/backend/config/database.php';

// Get logged-in student ID
$userId = (int) $_SESSION['user_id'];

// Get student details
$stmt = $pdo->prepare("
    SELECT name, profile_image
    FROM users
    WHERE id = ?
");

$stmt->execute([$userId]);

$topbarUser = $stmt->fetch(PDO::FETCH_ASSOC);

// Student name
$topbarName = $topbarUser['name'] ?? 'Student';

// Profile image
$profileImageUrl = null;

$projectRoot = dirname(__DIR__);

if (
    !empty($topbarUser['profile_image']) &&
    strpos($topbarUser['profile_image'], 'uploads/profiles/') === 0
) {

    $imageFileName = basename($topbarUser['profile_image']);

    $profileFile = $projectRoot
        . '/uploads/profiles/'
        . $imageFileName;

    if (is_file($profileFile)) {

        $profileImageUrl = '../../uploads/profiles/'
            . rawurlencode($imageFileName)
            . '?v='
            . filemtime($profileFile);

    }
}

// Default avatar letter
$firstLetter = strtoupper(substr($topbarName, 0, 1));

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>

<!-- Common Student Topbar -->

<header class="topbar">

    <span class="portal-label">
        STUDENT PORTAL
    </span>

    <div class="topbar-right">

        <!-- Student Profile -->

        <a href="profile.php" class="topbar-user">

            <?php if ($profileImageUrl): ?>

                <!-- Uploaded Profile Photo -->

                <img
                    src="<?= htmlspecialchars(
                        $profileImageUrl,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    alt="Student Profile Photo"
                    class="topbar-profile-photo"
                >

            <?php else: ?>

                <!-- Default Avatar -->

                <div class="topbar-avatar">

                    <?= htmlspecialchars(
                        $firstLetter,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </div>

            <?php endif; ?>

            <!-- Student Name -->

            <span class="topbar-user-name">

                <?= htmlspecialchars(
                    $topbarName,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </span>

        </a>

        <!-- Logout Form -->

        <form
            action="../../backend/auth/logout.php"
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

            <button type="submit" class="logout-button">
                Log Out
            </button>

        </form>

    </div>

</header>