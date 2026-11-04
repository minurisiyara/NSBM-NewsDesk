
<?php

// =============================================
// NSBM NEWSDESK - HOMEPAGE
// Sample content for frontend demonstration.
// Replace with database content during integration.
// =============================================


// FEATURED NEWS

$featuredNews = [
    "category" => "Academic",
    "title" => "Discover a World of Learning at University",
    "description" => "Explore the latest academic developments, student experiences and exciting opportunities from university life.",
    "date" => "Campus Feature",
    "reading_time" => "3 min read",
    "image" => "https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=1600&q=85"
];


// LATEST NEWS

$latestNews = [

    [
        "id" => 1,
        "category" => "Academic",
        "title" => "A New Chapter in Learning and Innovation",
        "description" => "Students and educators explore new ideas, technology and opportunities through academic collaboration.",
        "date" => "Sample story",
        "reading_time" => "4 min read",
        "image" => "https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=900&q=85"
    ],

    [
        "id" => 2,
        "category" => "Sports",
        "title" => "Celebrating Teamwork and Sporting Spirit",
        "description" => "Discover the excitement of university sports, teamwork and student participation.",
        "date" => "Sample story",
        "reading_time" => "3 min read",
        "image" => "https://images.unsplash.com/photo-1531415074968-036ba1b575da?auto=format&fit=crop&w=900&q=85"
    ],

    [
        "id" => 3,
        "category" => "Events",
        "title" => "Innovation Takes Centre Stage",
        "description" => "Creative ideas, demonstrations and collaboration bring the university community together.",
        "date" => "Sample story",
        "reading_time" => "5 min read",
        "image" => "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=85"
    ]

];


// SLIDESHOW

$featuredSlides = array_merge([$featuredNews], $latestNews);


// BREAKING NEWS

$breakingNews = [
    "Discover the latest stories, achievements and updates from the university community.",
    "Stay connected with academic news, student activities and campus events."
];


// ANNOUNCEMENTS
// Sample placeholders. Replace with verified announcements.

$announcements = [

    [
        "day" => "26",
        "month" => "SEP",
        "title" => "Academic Announcements",
        "details" => "Check official channels for academic updates."
    ],

    [
        "day" => "27",
        "month" => "SEP",
        "title" => "Career Opportunities",
        "details" => "Explore career guidance and opportunities."
    ],

    [
        "day" => "28",
        "month" => "SEP",
        "title" => "Student Activities",
        "details" => "Discover student activities and clubs."
    ],

    [
        "day" => "29",
        "month" => "SEP",
        "title" => "Research and Innovation",
        "details" => "Explore research-related updates."
    ],

    [
        "day" => "30",
        "month" => "SEP",
        "title" => "University Events",
        "details" => "Stay informed about campus events."
    ]

];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="NSBM NewsDesk - University news, student stories, academic updates and events.">

    <title>NSBM NewsDesk | Home</title>

    <!-- Homepage stylesheet -->
    <link rel="stylesheet" href="../assests/css/home.css">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>


<body>


<!-- =============================================
     HEADER / NAVIGATION
============================================= -->

<header class="navbar">

    <div class="navbar-inner">

        <!-- BRAND -->

        <a href="home.php" class="logo">

            <img
                src="../assests/images/nsbm-logo.png"
                alt="NSBM Logo"
            >

            <span>NSBM NewsDesk</span>

        </a>


        <!-- MOBILE MENU BUTTON -->

        <button
            class="mobile-menu-toggle"
            type="button"
            aria-label="Toggle navigation"
            aria-expanded="false"
        >

            <i class="fa-solid fa-bars"></i>

        </button>


        <!-- NAVIGATION LINKS -->

        <nav class="nav-links">

            <a href="home.php" class="active">Home</a>

            <a href="news.php">News</a>

            <a href="news.php?category=Academic">Academic</a>

            <a href="news.php?category=Sports">Sports</a>

            <a href="news.php?category=Events">Events</a>

            <a href="news.php?category=Gallery">Gallery</a>

            <a href="#">About</a>

        </nav>


        <!-- RIGHT SIDE ACTIONS -->

        <div class="nav-actions">


            <!-- SEARCH -->

            <form action="search.php" method="GET" class="search-form">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="search"
                    name="q"
                    placeholder="Search news, events..."
                    aria-label="Search news and events"
                >

            </form>


            <!-- LOGIN -->

            <a href="auth/login.php" class="login-btn">

                Login

            </a>


            <!-- SIGN UP -->

            <a href="auth/register.php" class="signup-btn">

                Sign Up

            </a>


        </div>

    </div>

</header>



<!-- =============================================
     BREAKING NEWS BAR
============================================= -->

<section class="breaking-news">

    <div class="breaking-inner">


        <div class="breaking-label">

            <i class="fa-solid fa-bolt"></i>

            <span>BREAKING NEWS</span>

        </div>


        <div class="breaking-divider"></div>


        <div class="breaking-text">

            <div class="breaking-track">

                <?php foreach ($breakingNews as $item): ?>

                    <span>
                        <?= htmlspecialchars($item) ?>
                    </span>

                <?php endforeach; ?>

            </div>

        </div>


        <div class="breaking-controls">

            <button
                type="button"
                class="breaking-prev"
                aria-label="Previous breaking news"
            >

                <i class="fa-solid fa-chevron-left"></i>

            </button>


            <button
                type="button"
                class="breaking-next"
                aria-label="Next breaking news"
            >

                <i class="fa-solid fa-chevron-right"></i>

            </button>

        </div>


    </div>

</section>



<!-- =============================================
     MAIN CONTENT
============================================= -->

<main class="main-container">


    <!-- WELCOME ROW -->

    <section class="welcome-row">

        <div class="welcome-label">

            THE CAMPUS, CONNECTED.

        </div>


        <div class="current-date">

            <i class="fa-regular fa-calendar"></i>

            <?= date("l, F j, Y") ?>

        </div>

    </section>



    <!-- =========================================
         FEATURED NEWS + ANNOUNCEMENTS
    ========================================== -->

    <section class="featured-layout">


        <!-- FEATURED SLIDESHOW -->

        <div class="featured-news">


            <?php foreach ($featuredSlides as $index => $slide): ?>


                <article

                    class="featured-slide <?= $index === 0 ? 'active' : '' ?>"

                    style="background-image: url('<?= htmlspecialchars($slide['image'], ENT_QUOTES, 'UTF-8') ?>');"

                    aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>"

                >


                    <div class="featured-overlay"></div>


                    <div class="featured-content">


                        <span class="category-label">

                            <?= htmlspecialchars($slide['category']) ?>

                        </span>


                        <h1>

                            <?= htmlspecialchars($slide['title']) ?>

                        </h1>


                        <p class="featured-description">

                            <?= htmlspecialchars($slide['description']) ?>

                        </p>


                        <div class="featured-meta">

                            <span>

                                <?= htmlspecialchars($slide['date']) ?>

                            </span>

                            <span class="meta-dot">•</span>

                            <span>

                                <?= htmlspecialchars($slide['reading_time']) ?>

                            </span>

                        </div>


                        <a href="news.php" class="read-story-btn">

                            Read Full Story

                            <i class="fa-solid fa-arrow-right"></i>

                        </a>


                    </div>


                </article>


            <?php endforeach; ?>



            <!-- SLIDESHOW ARROWS -->

            <button
                type="button"
                class="slide-arrow prev-slide"
                aria-label="Previous slide"
            >

                <i class="fa-solid fa-chevron-left"></i>

            </button>


            <button
                type="button"
                class="slide-arrow next-slide"
                aria-label="Next slide"
            >

                <i class="fa-solid fa-chevron-right"></i>

            </button>



            <!-- SLIDESHOW DOTS -->

            <div class="slide-dots">


                <?php foreach ($featuredSlides as $index => $slide): ?>


                    <button

                        type="button"

                        class="slide-dot <?= $index === 0 ? 'active' : '' ?>"

                        data-slide="<?= $index ?>"

                        aria-label="Go to slide <?= $index + 1 ?>"

                        aria-current="<?= $index === 0 ? 'true' : 'false' ?>"

                    ></button>


                <?php endforeach; ?>


            </div>


        </div>



        <!-- ANNOUNCEMENTS PANEL -->

        <aside class="announcements">


            <div class="section-heading">

                <h2>Announcements</h2>

                <a href="news.php">

                    View All

                </a>

            </div>



            <div class="announcement-list">


                <?php foreach ($announcements as $announcement): ?>


                    <article class="announcement-item">


                        <!-- DATE BOX -->

                        <div class="date-box">

                            <strong>

                                <?= htmlspecialchars($announcement['day']) ?>

                            </strong>

                            <span>

                                <?= htmlspecialchars($announcement['month']) ?>

                            </span>

                        </div>



                        <!-- ANNOUNCEMENT DETAILS -->

                        <div class="announcement-info">

                            <h3>

                                <?= htmlspecialchars($announcement['title']) ?>

                            </h3>

                            <p>

                                <?= htmlspecialchars($announcement['details']) ?>

                            </p>

                        </div>


                    </article>


                <?php endforeach; ?>


            </div>


        </aside>


    </section>



    <!-- =========================================
         LATEST NEWS
    ========================================== -->

    <section class="latest-section">


        <!-- SECTION TITLE -->

        <div class="section-heading latest-heading">


            <div class="latest-title">

                <span class="heading-accent"></span>

                <h2>Latest News</h2>

            </div>


            <a href="news.php" class="view-all-news">

                View All News

                <i class="fa-solid fa-arrow-right"></i>

            </a>


        </div>



        <!-- NEWS CARDS -->

        <div class="latest-grid">


            <?php foreach ($latestNews as $article): ?>


                <article class="news-card">


                    <!-- IMAGE -->

                    <a href="article.php?id=<?= (int) $article['id'] ?>" class="news-image-link">

                        <img

                            src="<?= htmlspecialchars($article['image'], ENT_QUOTES, 'UTF-8') ?>"

                            alt="<?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?>"

                            loading="lazy"

                        >

                    </a>



                    <!-- CARD CONTENT -->

                    <div class="news-card-content">


                        <span class="news-category">

                            <?= htmlspecialchars($article['category']) ?>

                        </span>


                        <h3>

                            <a href="article.php?id=<?= (int) $article['id'] ?>">

                                <?= htmlspecialchars($article['title']) ?>

                            </a>

                        </h3>


                        <p>

                            <?= htmlspecialchars($article['description']) ?>

                        </p>


                        <!-- CARD FOOTER -->

                        <div class="news-card-footer">

                            <span>

                                <?= htmlspecialchars($article['date']) ?>

                            </span>

                            <span>

                                <?= htmlspecialchars($article['reading_time']) ?>

                            </span>

                        </div>


                    </div>


                </article>


            <?php endforeach; ?>


        </div>


    </section>



    <!-- =========================================
         CATEGORY SHORTCUTS
    ========================================== -->

    <section class="categories-section">


        <div class="section-heading">

            <div class="latest-title">

                <span class="heading-accent"></span>

                <h2>Explore Categories</h2>

            </div>

        </div>



        <div class="category-grid">


            <a href="news.php?category=Academic" class="category-card">

                <span class="category-icon">

                    <i class="fa-solid fa-graduation-cap"></i>

                </span>

                <span>Academic</span>

                <i class="fa-solid fa-arrow-right"></i>

            </a>



            <a href="news.php?category=Events" class="category-card">

                <span class="category-icon">

                    <i class="fa-solid fa-calendar-days"></i>

                </span>

                <span>Events</span>

                <i class="fa-solid fa-arrow-right"></i>

            </a>



            <a href="news.php?category=Sports" class="category-card">

                <span class="category-icon">

                    <i class="fa-solid fa-trophy"></i>

                </span>

                <span>Sports</span>

                <i class="fa-solid fa-arrow-right"></i>

            </a>



            <a href="news.php?category=General" class="category-card">

                <span class="category-icon">

                    <i class="fa-solid fa-newspaper"></i>

                </span>

                <span>General</span>

                <i class="fa-solid fa-arrow-right"></i>

            </a>


        </div>


    </section>


</main>



<!-- =============================================
     FOOTER
============================================= -->

<footer class="site-footer">


    <div class="footer-inner">


        <!-- LEFT: BRAND INFORMATION -->

        <div class="footer-brand">


            <a href="home.php" class="footer-logo">

                <img

                    src="../assests/images/nsbm-logo.png"

                    alt="NSBM Green University Logo"

                    loading="lazy"

                >

                <span>NSBM NewsDesk</span>

            </a>



            <p class="footer-description">

                Your window into life at NSBM Green University.

                Stories, achievements and moments from our community.

            </p>



            <div class="footer-contact-links">

                <a href="mailto:newsdesk@nsbm.ac.lk">

                    Contact the newsroom

                    <i class="fa-solid fa-arrow-up-right-from-square"></i>

                </a>


                <a href="auth/login.php">

                    Submit a story

                    <i class="fa-solid fa-arrow-up-right-from-square"></i>

                </a>

            </div>



            <p class="copyright">

                &copy; <?= date("Y") ?> NSBM Green University.

                All rights reserved.

            </p>


        </div>



        <!-- RIGHT: QUICK LINKS PANEL -->

        <div class="footer-quick-links">


            <h3>

                <i class="fa-solid fa-link"></i>

                Quick Links

            </h3>



            <a
                href="https://www.nsbm.ac.lk/"
                target="_blank"
                rel="noopener noreferrer"
            >

                <i class="fa-solid fa-globe"></i>

                University Website

                <i class="fa-solid fa-arrow-up-right-from-square external-icon"></i>

            </a>



            <a href="auth/login.php">

                <i class="fa-solid fa-user-graduate"></i>

                Student Portal

                <i class="fa-solid fa-arrow-up-right-from-square external-icon"></i>

            </a>



            <a
                href="https://www.nsbm.ac.lk/"
                target="_blank"
                rel="noopener noreferrer"
            >

                <i class="fa-solid fa-calendar-days"></i>

                Academic Calendar

                <i class="fa-solid fa-arrow-up-right-from-square external-icon"></i>

            </a>



            <a
                href="https://www.nsbm.ac.lk/"
                target="_blank"
                rel="noopener noreferrer"
            >

                <i class="fa-solid fa-book-open"></i>

                Digital Library

                <i class="fa-solid fa-arrow-up-right-from-square external-icon"></i>

            </a>


        </div>


    </div>


</footer>



<!-- HOMEPAGE JAVASCRIPT -->

<script src="../assests/js/home.js"></script>


</body>

</html>
