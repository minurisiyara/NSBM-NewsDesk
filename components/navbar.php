<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<script>
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
});
</script>