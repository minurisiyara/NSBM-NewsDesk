<?php
declare(strict_types=1);

$activePage = $activePage ?? 'dashboard';
$adminName = $_SESSION['user_name'] ?? 'Admin User';
$adminInitial = strtoupper(substr($adminName, 0, 1) ?: 'A');

$projectRoot = dirname(__DIR__);
$logoRel = 'assets/images/nsbm-logo.png';
$logoVer = is_file($projectRoot . '/' . $logoRel) ? '?v=' . (string) filemtime($projectRoot . '/' . $logoRel) : '';
$nsbmLogoSrc = '../../' . $logoRel . $logoVer;
?>
<aside class="admin-sidebar side" id="adminSidebar">
    <div class="sidebar-brand">
        <div class="brand-top-row">
            <a class="brand" href="dashboard.php">
                <img
                    src="<?= htmlspecialchars($nsbmLogoSrc, ENT_QUOTES, 'UTF-8') ?>"
                    alt="NSBM Green University"
                    class="logo nsbm-logo-img"
                    onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'logo',textContent:'NSBM'}))"
                >
                <span class="brand-text">
                    <span>NSBM NewsDesk</span>
                    <small class="brand-sub">Admin</small>
                </span>
            </a>
            <button type="button" class="sidebar-toggle-btn burger" id="sidebarToggleBtn" title="Toggle Sidebar" aria-label="Toggle Sidebar">
                ☰
            </button>
        </div>
    </div>

    <div class="sidebar-user-card prof">
        <div class="avatar-circle av"><?= htmlspecialchars($adminInitial, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="user-meta">
            <h4><?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?></h4>
            <span>Administrator</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?= $activePage === 'dashboard' ? 'active on' : '' ?>" title="Dashboard">
            <span class="nav-icon">📊</span>
            <span class="nav-text">Dashboard</span>
        </a>
        <a href="news.php" class="nav-item <?= $activePage === 'news' ? 'active on' : '' ?>" title="News Management">
            <span class="nav-icon">📰</span>
            <span class="nav-text">News Management</span>
        </a>
        <a href="categories.php" class="nav-item <?= $activePage === 'categories' ? 'active on' : '' ?>" title="Categories">
            <span class="nav-icon">🏷️</span>
            <span class="nav-text">Categories</span>
        </a>
        <a href="announcements.php" class="nav-item <?= $activePage === 'announcements' ? 'active on' : '' ?>" title="Announcements">
            <span class="nav-icon">📢</span>
            <span class="nav-text">Announcements</span>
        </a>
        <a href="comments.php" class="nav-item <?= $activePage === 'comments' ? 'active on' : '' ?>" title="Comments">
            <span class="nav-icon">💬</span>
            <span class="nav-text">Comments</span>
        </a>
        <a href="reports.php" class="nav-item <?= $activePage === 'reports' ? 'active on' : '' ?>" title="Reports">
            <span class="nav-icon">📈</span>
            <span class="nav-text">Reports</span>
        </a>
        <a href="users.php" class="nav-item <?= $activePage === 'users' ? 'active on' : '' ?>" title="Users">
            <span class="nav-icon">👥</span>
            <span class="nav-text">Users</span>
        </a>
    </nav>
</aside>