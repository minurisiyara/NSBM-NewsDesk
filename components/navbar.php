<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-detect base path whether included from root (index.php), /pages/*.php, or /pages/<role>/*.php
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$inPagesDir = strpos($scriptPath, '/pages/') !== false;
$inSubFolder = preg_match('#/pages/[^/]+/[^/]+\.php$#', $scriptPath) === 1;

$basePath = $inSubFolder ? '../../' : ($inPagesDir ? '../' : '');
$pagesPath = $basePath . 'pages/';

$currentPage = basename($_SERVER['PHP_SELF']);
$currentCategory = $_GET['category'] ?? '';

$isLoggedIn = !empty($_SESSION['user_id']) || !empty($_SESSION['username']);
$displayName = $_SESSION['username'] ?? $_SESSION['name'] ?? 'Student';

// Detect role (admin vs student) from common session keys
$rawRole = $_SESSION['role'] ?? $_SESSION['user_role'] ?? $_SESSION['account_type'] ?? 'student';
$role = strtolower(trim((string) $rawRole));
$isAdmin = ($role === 'admin' || $role === 'super_admin' || !empty($_SESSION['is_admin']));

// Set role-based dashboard URL
$dashboardUrl = $isAdmin
    ? $pagesPath . 'admin/dashboard.php'
    : $pagesPath . 'student/dashboard.php';
?>

<header class="site-navbar">
    <div class="nav-container">
        <!-- Brand Logo -->
        <a href="<?php echo $basePath; ?>index.php" class="nav-brand">
            <img src="<?php echo $basePath; ?>assets/images/nsbm-logo.png" alt="NSBM Logo" class="brand-logo" onerror="this.style.display='none'">
            <span class="brand-title">NewsDesk</span>
        </a>

        <!-- Mobile Menu Toggle -->
        <button type="button" class="nav-mobile-toggle" id="navMobileToggle" aria-label="Toggle Menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Navigation Links + Search + Auth -->
        <div class="nav-collapse" id="navCollapse">
            <nav class="nav-links">
                <a href="<?php echo $basePath; ?>index.php" class="nav-link <?php echo ($currentPage === 'index.php' || ($currentPage === 'home.php' && $currentCategory === '')) ? 'active' : ''; ?>">Home</a>
                <a href="<?php echo $pagesPath; ?>news.php" class="nav-link <?php echo ($currentPage === 'news.php' && $currentCategory === '') ? 'active' : ''; ?>">News</a>
                <a href="<?php echo $pagesPath; ?>news.php?category=Academic" class="nav-link <?php echo ($currentCategory === 'Academic') ? 'active' : ''; ?>">Academic</a>
                <a href="<?php echo $pagesPath; ?>news.php?category=Sports" class="nav-link <?php echo ($currentCategory === 'Sports') ? 'active' : ''; ?>">Sports</a>
                <a href="<?php echo $pagesPath; ?>news.php?category=Events" class="nav-link <?php echo ($currentCategory === 'Events') ? 'active' : ''; ?>">Events</a>
                <a href="<?php echo $pagesPath; ?>news.php?category=Gallery" class="nav-link <?php echo ($currentCategory === 'Gallery') ? 'active' : ''; ?>">Gallery</a>
            </nav>

            <!-- Search Bar -->
            <form action="<?php echo $pagesPath; ?>search.php" method="GET" class="nav-search-form">
                <input type="text" name="q" class="nav-search-input" placeholder="Search news, events..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" required>
                <button type="submit" class="nav-search-btn" aria-label="Search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </form>

            <!-- Auth Area: Profile Icon Dropdown OR Login Button -->
            <div class="nav-auth-area">
                <?php if ($isLoggedIn): ?>
                    <div class="profile-dropdown-wrapper" id="profileDropdownWrapper">
                        <button type="button" class="profile-icon-btn" id="profileMenuBtn" aria-label="User Profile" aria-expanded="false" title="<?php echo htmlspecialchars($displayName); ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </button>

                        <div class="profile-dropdown-menu" id="profileDropdownMenu">
                            <div class="profile-dropdown-header">
                                <span class="profile-user-name"><?php echo htmlspecialchars($displayName); ?></span>
                            </div>
                            <a href="<?php echo htmlspecialchars($dashboardUrl); ?>" class="profile-dropdown-item">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <?php echo $isAdmin ? 'Admin Dashboard' : 'View Profile'; ?>
                            </a>
                            <a href="<?php echo $pagesPath; ?>auth/logout.php" class="profile-dropdown-item logout-item">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $pagesPath; ?>auth/login.php" class="nav-login-btn">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const profileBtn = document.getElementById('profileMenuBtn');
    const profileMenu = document.getElementById('profileDropdownMenu');

    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = profileMenu.classList.toggle('is-open');
            profileBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', function (e) {
            if (!profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
                profileMenu.classList.remove('is-open');
                profileBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    const mobileToggle = document.getElementById('navMobileToggle');
    const navCollapse = document.getElementById('navCollapse');

    if (mobileToggle && navCollapse) {
        mobileToggle.addEventListener('click', function () {
            const isOpen = navCollapse.classList.toggle('is-open');
            mobileToggle.classList.toggle('is-active', isOpen);
            mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }
});
</script>