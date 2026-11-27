
<?php

// Ensure active page fallback is set
$activePage = $activePage ?? 'dashboard';

// Logged-in administrator details
$adminName = $_SESSION['user_name'] ?? 'Admin User';
$adminInitial = strtoupper(substr($adminName, 0, 1));

?>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="admin-sidebar" id="adminSidebar">

    <!-- Sidebar Header / Branding -->
    <div class="sidebar-header sidebar-brand">

        <div class="brand">

            <img
                src="../../assets/images/nsbm-logo.png"
                alt="NSBM Logo"
                class="brand-logo"
            >

            <div class="brand-info">
                <span class="brand-title">NSBM NewsDesk</span>
                <span class="brand-tag">Admin</span>
            </div>

        </div>

        <button
            type="button"
            class="toggle-btn sidebar-toggle-btn"
            id="sidebarToggleBtn"
            title="Toggle Sidebar"
            aria-label="Toggle Sidebar"
        >
            ☰
        </button>

    </div>

    <!-- Logged-in Admin Profile Card -->
    <div class="sidebar-user-card">

        <div class="avatar-circle">
            <?= htmlspecialchars($adminInitial, ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="user-meta">
            <h4>
                <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?>
            </h4>

            <span>Administrator</span>
        </div>

    </div>

    <!-- Admin Navigation -->
    <nav class="sidebar-nav">

        <a
            href="dashboard.php"
            class="nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>"
            title="Dashboard"
        >
            <span class="nav-icon">📊</span>
            <span class="nav-text">Dashboard</span>
        </a>

        <a
            href="news.php"
            class="nav-item <?= $activePage === 'news' ? 'active' : '' ?>"
            title="News Management"
        >
            <span class="nav-icon">📰</span>
            <span class="nav-text">News Management</span>
        </a>

        <a
            href="categories.php"
            class="nav-item <?= $activePage === 'categories' ? 'active' : '' ?>"
            title="Categories"
        >
            <span class="nav-icon">🏷️</span>
            <span class="nav-text">Categories</span>
        </a>

        <a
            href="announcements.php"
            class="nav-item <?= $activePage === 'announcements' ? 'active' : '' ?>"
            title="Announcements"
        >
            <span class="nav-icon">📢</span>
            <span class="nav-text">Announcements</span>
        </a>

        <a
            href="comments.php"
            class="nav-item <?= $activePage === 'comments' ? 'active' : '' ?>"
            title="Comments"
        >
            <span class="nav-icon">💬</span>
            <span class="nav-text">Comments</span>
        </a>

        <a
            href="reports.php"
            class="nav-item <?= $activePage === 'reports' ? 'active' : '' ?>"
            title="Reports"
        >
            <span class="nav-icon">📈</span>
            <span class="nav-text">Reports</span>
        </a>

        <a
            href="users.php"
            class="nav-item <?= $activePage === 'users' ? 'active' : '' ?>"
            title="Users"
        >
            <span class="nav-icon">👥</span>
            <span class="nav-text">Users</span>
        </a>

    </nav>

</aside>
