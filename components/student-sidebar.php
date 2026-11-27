<?php

// Get active page
$activePage = $activePage ?? '';

?>

<!-- Student Sidebar -->

<aside class="sidebar">

    <!-- NSBM Logo and Brand -->

    <div class="brand">

        <img
            src="../../assets/images/nsbm-logo.png"
            alt="NSBM Green University Logo"
            class="brand-logo"
        >

        <div class="brand-text">

            <h2>NSBM</h2>

            <span>NewsDesk Portal</span>

        </div>

    </div>


    <!-- Sidebar Navigation -->

    <nav class="sidebar-nav">

        <a
            href="dashboard.php"
            class="<?= $activePage === 'dashboard' ? 'active' : '' ?>"
        >
            Dashboard
        </a>


        <a
            href="my-submissions.php"
            class="<?= $activePage === 'my-submissions' ? 'active' : '' ?>"
        >
            My Submissions
        </a>


        <a
            href="submit-article.php"
            class="<?= $activePage === 'submit-article' ? 'active' : '' ?>"
        >
            Submit Article
        </a>


        <a
            href="bookmarks.php"
            class="<?= $activePage === 'bookmarks' ? 'active' : '' ?>"
        >
            Bookmarks
        </a>


        <a
            href="profile.php"
            class="<?= $activePage === 'profile' ? 'active' : '' ?>"
        >
            My Profile
        </a>

    </nav>


    <!-- Sidebar Bottom -->

    <div class="sidebar-bottom">

        <a href="../../index.php">
            Back to Journal
        </a>

        <span>NSBM Green University</span>

    </div>

</aside>