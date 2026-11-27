<?php

declare(strict_types=1);

// Session
require_once dirname(__DIR__, 2) . '/backend/config/session.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Check student role
if (($_SESSION['user_role'] ?? '') !== 'student') {
    http_response_code(403);
    exit('Access denied.');
}

// Student details
$name = $_SESSION['user_name'] ?? 'Student';
$userId = (int) $_SESSION['user_id'];

// Active sidebar page
$activePage = 'dashboard';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database connection
require_once dirname(__DIR__, 2) . '/backend/config/database.php';


// ==========================================
// GET ARTICLE STATISTICS
// ==========================================

$stmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        COALESCE(SUM(status IN ('approved', 'published')), 0) AS approved,
        COALESCE(SUM(status = 'pending'), 0) AS pending,
        COALESCE(SUM(status = 'rejected'), 0) AS rejected
    FROM articles
    WHERE author_id = ?
");

$stmt->execute([$userId]);

$stats = $stmt->fetch(PDO::FETCH_ASSOC);


// ==========================================
// GET RECENT SUBMISSIONS
// ==========================================

$stmt = $pdo->prepare("
    SELECT
        a.title,
        a.status,
        a.created_at,
        c.name AS category
    FROM articles a
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE a.author_id = ?
    ORDER BY a.created_at DESC
    LIMIT 4
");

$stmt->execute([$userId]);

$recentArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// GET BOOKMARK COUNT
// ==========================================

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM bookmarks
    WHERE user_id = ?
");

$stmt->execute([$userId]);

$savedCount = (int) $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard | NSBM NewsDesk</title>

    <!-- Main Student CSS -->

    <link
        rel="stylesheet"
        href="../../assets/css/student.css"
    >

    <!-- Student Logo CSS -->

    <link
        rel="stylesheet"
        href="../../assets/css/student-logo.css?v=3"
    >

    <!-- Student Topbar CSS -->

    <link
        rel="stylesheet"
        href="../../assets/css/student-topbar.css?v=2"
    >

</head>

<body>


    <!-- ======================================
         COMMON STUDENT SIDEBAR
    ======================================= -->

    <?php

    include dirname(__DIR__, 2) . '/components/student-sidebar.php';

    ?>


    <!-- ======================================
         MAIN CONTENT
    ======================================= -->

    <div class="main-content">


        <!-- ==================================
             COMMON STUDENT TOPBAR
        =================================== -->

        <?php

        include dirname(__DIR__, 2) . '/components/student-topbar.php';

        ?>


        <!-- Dashboard Content -->

        <main class="dashboard-content">


            <!-- ==================================
                 WELCOME BANNER
            =================================== -->

            <section class="welcome-banner">

                <span class="welcome-label">
                    STUDENT DASHBOARD
                </span>

                <h1>

                    Welcome back,

                    <?= htmlspecialchars(
                        $name,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>!

                </h1>

                <p>
                    Manage your academic articles, track editorial progress
                    and explore research opportunities through NSBM NewsDesk.
                </p>

                <div class="welcome-actions">

                    <a
                        href="my-submissions.php"
                        class="secondary-button"
                    >
                        View My Submissions
                    </a>

                    <a
                        href="submit-article.php"
                        class="primary-button"
                    >
                        + Submit New Article
                    </a>

                </div>

            </section>


            <!-- ==================================
                 ARTICLE STATISTICS
            =================================== -->

            <section class="statistics-section">

                <div class="section-heading">

                    <div>

                        <span>YOUR PERFORMANCE</span>

                        <h2>Article Overview</h2>

                    </div>

                </div>


                <div class="statistics-grid">


                    <!-- Total Articles -->

                    <div class="stat-card">

                        <div class="stat-icon blue">
                            <span>▤</span>
                        </div>

                        <h3>Total Articles</h3>

                        <p class="stat-number">
                            <?= (int) $stats['total'] ?>
                        </p>

                        <span class="stat-description">
                            Total manuscripts submitted
                        </span>

                    </div>


                    <!-- Approved Articles -->

                    <div class="stat-card">

                        <div class="stat-icon green">
                            <span>✓</span>
                        </div>

                        <h3>Approved & Published</h3>

                        <p class="stat-number">
                            <?= (int) $stats['approved'] ?>
                        </p>

                        <span class="stat-description">
                            Successfully approved articles
                        </span>

                    </div>


                    <!-- Pending Articles -->

                    <div class="stat-card">

                        <div class="stat-icon yellow">
                            <span>◷</span>
                        </div>

                        <h3>Pending Peer Review</h3>

                        <p class="stat-number">
                            <?= (int) $stats['pending'] ?>
                        </p>

                        <span class="stat-description">
                            Waiting for editorial review
                        </span>

                    </div>


                    <!-- Rejected Articles -->

                    <div class="stat-card">

                        <div class="stat-icon red">
                            <span>!</span>
                        </div>

                        <h3>Rejected Articles</h3>

                        <p class="stat-number">
                            <?= (int) $stats['rejected'] ?>
                        </p>

                        <span class="stat-description">
                            Rejected articles
                        </span>

                    </div>


                </div>

            </section>


            <!-- ==================================
                 DASHBOARD MAIN GRID
            =================================== -->

            <div class="dashboard-grid">


                <!-- Recent Submissions -->

                <section class="recent-section">

                    <div class="section-heading">

                        <div>

                            <span>MANUSCRIPT FLOW</span>

                            <h2>
                                Recent Submissions & Editorial Status
                            </h2>

                        </div>

                        <a href="my-submissions.php">
                            View All →
                        </a>

                    </div>


                    <?php if (!empty($recentArticles)): ?>

                        <div class="submission-list">

                            <?php foreach ($recentArticles as $article): ?>

                                <article class="submission-card">

                                    <div class="submission-info">

                                        <span class="category-label">

                                            <?= htmlspecialchars(
                                                $article['category'] ?? 'Uncategorized',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </span>

                                        <h3>

                                            <?= htmlspecialchars(
                                                $article['title'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </h3>

                                        <p>

                                            Submitted on

                                            <?= htmlspecialchars(
                                                date(
                                                    'M d, Y',
                                                    strtotime($article['created_at'])
                                                ),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </p>

                                    </div>


                                    <!-- Article Status -->

                                    <span class="status-badge <?= htmlspecialchars(
                                        $article['status'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>">

                                        <?= htmlspecialchars(
                                            ucfirst($article['status']),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </span>

                                </article>

                            <?php endforeach; ?>

                        </div>


                    <?php else: ?>


                        <!-- Empty State -->

                        <div class="empty-state">

                            <h3>No submissions yet</h3>

                            <p>
                                Start your academic publishing journey
                                by submitting your first article.
                            </p>

                            <a
                                href="submit-article.php"
                                class="primary-button"
                            >
                                Submit Your First Article
                            </a>

                        </div>


                    <?php endif; ?>

                </section>


                <!-- ==================================
                     RIGHT INFORMATION PANEL
                =================================== -->

                <aside class="information-panel">


                    <!-- Bookmarks -->

                    <div class="info-card">

                        <h3>Bookmarks</h3>

                        <p class="saved-number">
                            <?= $savedCount ?>
                        </p>

                        <p>
                            Articles saved to your reading list.
                        </p>

                        <a href="bookmarks.php">
                            Manage Bookmarks →
                        </a>

                    </div>


                    <!-- Editorial Guidance -->

                    <div class="info-card help-card">

                        <h3>Need Editorial Guidance?</h3>

                        <p>
                            Make sure your articles follow the
                            university's editorial guidelines.
                        </p>

                        <a href="../../index.php">
                            Visit NewsDesk →
                        </a>

                    </div>


                </aside>

            </div>

        </main>

    </div>

</body>

</html>