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

function admin_asset_url(string $relativePath): string
{
    $projectRoot = dirname(__DIR__);
    $cleanRel = ltrim(str_replace('\\', '/', $relativePath), '/');
    $fullPath = $projectRoot . '/' . $cleanRel;
    $version = is_file($fullPath) ? '?v=' . (string) filemtime($fullPath) : '';

    return '../../' . $cleanRel . $version;
}

function admin_render_start(string $activePage, string $pageTitle): void
{
    $authUser = admin_require_auth();
    $initial = strtoupper(substr($authUser['name'], 0, 1) ?: 'A');
    $adminCssHref = admin_asset_url('assets/css/admin.css');
    $nsbmLogoHref = admin_asset_url('assets/images/nsbm-logo.png');

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
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="color-scheme" content="light">
        <title><?= admin_h($pageTitle) ?> | NSBM NewsDesk Admin</title>
        <link rel="stylesheet" href="<?= admin_h($adminCssHref) ?>">
    </head>
    <body>
    <div class="adm">
        <div class="side-backdrop" id="sidebarBackdrop"></div>
        <aside class="side" id="adminSidebar">
            <a class="brand" href="dashboard.php">
                <img
                    src="<?= admin_h($nsbmLogoHref) ?>"
                    alt="NSBM Green University"
                    class="logo nsbm-logo-img"
                    onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'logo',textContent:'NSBM'}))"
                >
                <span class="brand-text">
                    <span>NSBM NewsDesk</span>
                    <small class="brand-sub">Admin</small>
                </span>
            </a>
                <button
                    type="button"
                    class="sidebar-toggle-btn"
                    id="sidebarToggleBtn"
                    title="Toggle Sidebar"
                    aria-label="Toggle Sidebar"
                >
                    &#9776;
                </button>

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
            <button type="button" class="burger" id="sidebarBurger" aria-label="Toggle Menu">&#9776;</button>
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
    $adminJsHref = admin_asset_url('assets/js/admin.js');
    ?>
            </main>
        </div>
    </div>
    <?php if ($includeCharts): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
    <?php endif; ?>
    <script src="<?= admin_h($adminJsHref) ?>"></script>
    <script>
    (function () {
        const sidebar = document.getElementById('adminSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const burger = document.getElementById('sidebarBurger');
        const toggleBtn = document.getElementById('sidebarToggleBtn');

        if (!sidebar) return;

        const storageKey = 'nsbm-admin-sidebar-collapsed';
        const isMobile = () => window.matchMedia('(max-width: 820px)').matches;

        // Restore saved desktop sidebar state
        try {
            if (!isMobile() && localStorage.getItem(storageKey) === 'true') {
                document.documentElement.classList.add('sidebar-collapsed');
                document.body.classList.add('sidebar-collapsed');
            }
        } catch (error) {
            console.warn('Could not restore sidebar preference:', error);
        }

        // Desktop collapse / expand
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                if (isMobile()) {
                    setDrawer(!sidebar.classList.contains('open'));
                    return;
                }

                const collapsed = document.body.classList.toggle('sidebar-collapsed');
                document.documentElement.classList.toggle('sidebar-collapsed', collapsed);

                try {
                    localStorage.setItem(storageKey, String(collapsed));
                } catch (error) {
                    console.warn('Could not save sidebar preference:', error);
                }
            });
        }

        // Mobile drawer
        function setDrawer(open) {
            sidebar.classList.toggle('open', open);

            if (backdrop) {
                backdrop.classList.toggle('show', open);
            }

            document.body.style.overflow = open ? 'hidden' : '';
        }

        if (burger) {
            burger.addEventListener('click', () => {
                setDrawer(!sidebar.classList.contains('open'));
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', () => setDrawer(false));
        }

        sidebar.querySelectorAll('nav a').forEach((link) => {
            link.addEventListener('click', () => setDrawer(false));
        });

        // Close the mobile drawer when switching to desktop
        window.addEventListener('resize', () => {
            if (!isMobile()) {
                setDrawer(false);
            }
        });
    })();
    </script>
    </body>
    </html>
    <?php
}
