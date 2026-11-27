<?php
$activePage = $activePage ?? 'dashboard';
?>
<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="brand">
            <span class="brand-title">NSBM Desk</span>
            <span class="brand-tag">Admin</span>
        </div>
        <button type="button" class="toggle-btn" id="sidebarToggleBtn" title="Toggle Sidebar">
            ☰
        </button>
    </div>
    <nav><?php
$activePage = $activePage ?? 'dashboard';
?>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="admin-sidebar" id="adminSidebar">

    <div class="sidebar-header">

        <div class="brand">
            <img src="../../assets/images/nsbm-logo.png"
                 alt="NSBM Logo"
                 class="brand-logo">

            <div class="brand-info">
                <span class="brand-title">NSBM Desk</span>
                <span class="brand-tag">Admin</span>
            </div>
        </div>

        <button type="button" class="toggle-btn"
                id="sidebarToggleBtn" title="Toggle Sidebar">
            ☰
        </button>

    </div>

    <nav>
        <ul>
            <li>
                <a href="dashboard.php" class="<?php echo ($activePage === 'dashboard') ? 'active' : ''; ?>">
                    <span class="nav-icon">📌</span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <li>
                <a href="news.php" class="<?php echo ($activePage === 'news') ? 'active' : ''; ?>">
                    <span class="nav-icon">📰</span>
                    <span class="nav-text">News Management</span>
                </a>
            </li>

            <li>
                <a href="categories.php" class="<?php echo ($activePage === 'categories') ? 'active' : ''; ?>">
                    <span class="nav-icon">📁</span>
                    <span class="nav-text">Categories</span>
                </a>
            </li>

            <li>
                <a href="announcements.php" class="<?php echo ($activePage === 'announcements') ? 'active' : ''; ?>">
                    <span class="nav-icon">📢</span>
                    <span class="nav-text">Announcements</span>
                </a>
            </li>

            <li>
                <a href="reports.php" class="<?php echo ($activePage === 'reports') ? 'active' : ''; ?>">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Reports</span>
                </a>
            </li>
        </ul>
    </nav>

</aside>
        <ul>
            <li>
                <a href="dashboard.php" class="<?php echo ($activePage === 'dashboard') ? 'active' : ''; ?>">
                    <span class="nav-icon">📌</span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="news.php" class="<?php echo ($activePage === 'news') ? 'active' : ''; ?>">
                    <span class="nav-icon">📰</span>
                    <span class="nav-text">News Management</span>
                </a>
            </li>
            <li>
                <a href="categories.php" class="<?php echo ($activePage === 'categories') ? 'active' : ''; ?>">
                    <span class="nav-icon">📁</span>
                    <span class="nav-text">Categories</span>
                </a>
            </li>
            <li>
                <a href="announcements.php" class="<?php echo ($activePage === 'announcements') ? 'active' : ''; ?>">
                    <span class="nav-icon">📢</span>
                    <span class="nav-text">Announcements</span>
                </a>
            </li>
            <li>
                <a href="reports.php" class="<?php echo ($activePage === 'reports') ? 'active' : ''; ?>">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Reports</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>