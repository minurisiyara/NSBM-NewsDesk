<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    require_once dirname(__DIR__) . '/backend/config/session.php';
}

function admin_require_auth(): array
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../auth/login.php');
        exit;
    }
    $role = strtolower((string) ($_SESSION['user_role'] ?? ''));
    if ($role !== 'admin' && $role !== 'moderator') {
        http_response_code(403);
        exit('Access denied.');
    }
    return [
        'name' => (string) ($_SESSION['user_name'] ?? 'Admin User'),
        'role' => ucfirst($role !== '' ? $role : 'Administrator'),
    ];
}

function admin_h(mixed $val): string
{
    return htmlspecialchars((string) ($val ?? ''), ENT_QUOTES, 'UTF-8');
}

function admin_render_start(string $activePage, string $pageTitle): void
{
    $authUser = admin_require_auth();
    $initial = strtoupper(substr($authUser['name'], 0, 1) ?: 'A');
    $navItems = [
        ['dashboard.php', 'Dashboard'],
        ['news.php', 'News Management'],
        ['categories.php', 'Categories'],
        ['announcements.php', 'Announcements'],
        ['comments.php', 'Comments'],
        ['reports.php', 'Reports'],
        ['users.php', 'Users'],
    ];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= admin_h($pageTitle) ?> | NSBM NewsDesk Admin</title>
        <link rel="stylesheet" href="../../assets/css/admin.css">
    </head>
    <body>
    <div class="adm">
        <aside class="side" id="adminSidebar">
            <a class="brand" href="dashboard.php">
                <span class="logo">NN</span>
                <span>NSBM NewsDesk Admin</span>
            </a>

            <div class="prof">
                <span class="av"><?= admin_h($initial) ?></span>
                <div>
                    <b><?= admin_h($authUser['name']) ?></b>
                    <small><?= admin_h($authUser['role']) ?></small>
                </div>
            </div>

            <nav>
                <?php foreach ($navItems as [$href, $label]): ?>
                    <a href="<?= admin_h($href) ?>" class="<?= $activePage === $href ? 'on' : '' ?>">
                        <?= admin_h($label) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <div class="main">
            <header class="top">
                <button type="button" class="burger" id="sidebarBurger" aria-label="Toggle Menu">☰</button>
                <h2><?= admin_h($pageTitle) ?></h2>
                <div class="topbar-right">
                    <span class="av"><?= admin_h($initial) ?></span>
                    <a class="btn o s" href="../../index.php">View Site</a>
                    <form action="../../backend/auth/logout.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= admin_h($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn r s">Logout</button>
                    </form>
                </div>
            </header>

            <main class="pg">
    <?php
}

function admin_render_end(bool $includeCharts = false): void
{
    ?>
            </main>
        </div>
    </div>
    <?php if ($includeCharts): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
    <?php endif; ?>
    <script src="../../assets/js/admin.js"></script>
    </body>
    </html>
    <?php
}
