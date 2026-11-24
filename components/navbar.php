<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

<<<<<<< HEAD
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$activeCat = trim((string) ($_GET['category'] ?? ''));
$currentSearch = trim((string) ($_GET['q'] ?? ''));
?>
<header class="navbar" id="siteNavbar">
    <div class="navbar-top">
        <a href="home.php" class="logo">
            <img src="../assets/images/nsbm-logo.png" alt="NSBM Green University Logo">
            <span>NewsDesk</span>
        </a>

        <button type="button"
                class="nav-toggle-btn"
                id="navToggleBtn"
                aria-label="Toggle navigation menu"
                aria-expanded="false">
            <span class="hamburger-bar"></span>
            <span class="hamburger-bar"></span>
            <span class="hamburger-bar"></span>
        </button>
    </div>

    <div class="navbar-collapse" id="navbarCollapse">
        <div class="navbar-collapse-inner">
            <nav class="nav-links">
                <a href="home.php" class="<?= $currentPage === 'home.php' ? 'active' : '' ?>">Home</a>
                <a href="news.php" class="<?= ($currentPage === 'news.php' && $activeCat === '') ? 'active' : '' ?>">News</a>
                <a href="news.php?category=Academic" class="<?= strcasecmp($activeCat, 'Academic') === 0 ? 'active' : '' ?>">Academic</a>
                <a href="news.php?category=Sports" class="<?= strcasecmp($activeCat, 'Sports') === 0 ? 'active' : '' ?>">Sports</a>
                <a href="news.php?category=Events" class="<?= strcasecmp($activeCat, 'Events') === 0 ? 'active' : '' ?>">Events</a>
                <a href="news.php?category=Gallery" class="<?= strcasecmp($activeCat, 'Gallery') === 0 ? 'active' : '' ?>">Gallery</a>
            </nav>

            <div class="nav-actions">
                <form action="search.php" method="GET" class="search-form">
                    <input type="search"
                           name="q"
                           value="<?= htmlspecialchars($currentSearch, ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Search news, events...">
                    <button type="submit" aria-label="Search">⌕</button>
                </form>

                <?php if (!empty($_SESSION['user_id'])): ?>
                <?php
                    $displayName = $_SESSION['full_name'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? 'User';
                    $userRole = strtolower((string) ($_SESSION['role'] ?? 'user'));
                    $profileHref = in_array($userRole, ['admin', 'moderator'], true) ? 'admin/dashboard.php' : 'home.php';
                ?>
                <a href="<?= htmlspecialchars($profileHref, ENT_QUOTES, 'UTF-8') ?>" class="signup-btn">
                    Hi, <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>
                </a>
                <?php else: ?>
                    <a href="auth/login.php" class="login-btn">Login</a>
                    <a href="auth/register.php" class="signup-btn">Sign Up</a>
=======
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
>>>>>>> feat/navbar-auth-slider
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<script>
<<<<<<< HEAD
document.addEventListener('DOMContentLoaded', () => {
    const navbar = document.getElementById('siteNavbar');
    const toggleBtn = document.getElementById('navToggleBtn');
    const navCollapse = document.getElementById('navbarCollapse');
    if (!navbar || !toggleBtn || !navCollapse) return;

    function closeMenu() {
        navCollapse.classList.remove('is-open');
        toggleBtn.classList.remove('is-open');
        toggleBtn.setAttribute('aria-expanded', 'false');
    }

    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = navCollapse.classList.toggle('is-open');
        toggleBtn.classList.toggle('is-open', isOpen);
        toggleBtn.setAttribute('aria-expanded', String(isOpen));
    });

    navCollapse.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeMenu);
    });

    document.addEventListener('click', (e) => {
        if (navCollapse.classList.contains('is-open') && !navbar.contains(e.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && navCollapse.classList.contains('is-open')) {
            closeMenu();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 960 && navCollapse.classList.contains('is-open')) {
            closeMenu();
        }
    });
=======
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
>>>>>>> feat/navbar-auth-slider
});
</script>