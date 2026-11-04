// ==========================================
// NSBM NEWSDESK - HOMEPAGE JAVASCRIPT
// ==========================================

document.addEventListener("DOMContentLoaded", function () {

    // 1. FEATURED NEWS SLIDESHOW

    const slideshow = document.querySelector(".featured-news");

    if (slideshow) {
        const slides = slideshow.querySelectorAll(".featured-slide");
        const dots = slideshow.querySelectorAll(".slide-dot");
        const previousButton = slideshow.querySelector(".prev-slide");
        const nextButton = slideshow.querySelector(".next-slide");

        let currentSlide = 0;
        let slideInterval = null;

        const reducedMotion = window.matchMedia(
            "(prefers-reduced-motion: reduce)"
        ).matches;

        function showSlide(index) {
            if (slides.length === 0) return;

            currentSlide = (index + slides.length) % slides.length;

            slides.forEach(function (slide, i) {
                const isActive = i === currentSlide;
                slide.classList.toggle("active", isActive);
                slide.setAttribute("aria-hidden", isActive ? "false" : "true");
            });

            dots.forEach(function (dot, i) {
                const isActive = i === currentSlide;
                dot.classList.toggle("active", isActive);

                if (isActive) {
                    dot.setAttribute("aria-current", "true");
                } else {
                    dot.removeAttribute("aria-current");
                }
            });
        }

        function nextSlide() {
            showSlide(currentSlide + 1);
        }

        function previousSlide() {
            showSlide(currentSlide - 1);
        }

        function stopSlideshow() {
            clearInterval(slideInterval);
            slideInterval = null;
        }

        function startSlideshow() {
            stopSlideshow();

            if (reducedMotion || slides.length < 2) return;

            slideInterval = setInterval(function () {
                nextSlide();
            }, 5000);
        }

        if (previousButton) {
            previousButton.addEventListener("click", function () {
                previousSlide();
                startSlideshow();
            });
        }

        if (nextButton) {
            nextButton.addEventListener("click", function () {
                nextSlide();
                startSlideshow();
            });
        }

        dots.forEach(function (dot, index) {
            dot.addEventListener("click", function () {
                showSlide(index);
                startSlideshow();
            });
        });

        slideshow.addEventListener("mouseenter", stopSlideshow);
        slideshow.addEventListener("mouseleave", startSlideshow);
        slideshow.addEventListener("focusin", stopSlideshow);

        slideshow.addEventListener("focusout", function (event) {
            if (!slideshow.contains(event.relatedTarget)) {
                startSlideshow();
            }
        });

        showSlide(0);
        startSlideshow();
    }


    // 2. BREAKING NEWS CONTROLS

    const breakingTrack = document.querySelector(".breaking-track");
    const breakingPrevious = document.querySelector(".breaking-prev");
    const breakingNext = document.querySelector(".breaking-next");

    if (breakingTrack) {
        const breakingItems = breakingTrack.querySelectorAll("span");
        let currentBreaking = 0;

        function showBreaking(index) {
            if (breakingItems.length === 0) return;

            currentBreaking = (index + breakingItems.length) % breakingItems.length;

            breakingItems.forEach(function (item, i) {
                item.style.display = i === currentBreaking ? "inline" : "none";
            });
        }

        if (breakingPrevious) {
            breakingPrevious.addEventListener("click", function () {
                showBreaking(currentBreaking - 1);
            });
        }

        if (breakingNext) {
            breakingNext.addEventListener("click", function () {
                showBreaking(currentBreaking + 1);
            });
        }

        showBreaking(0);
    }


    // 3. MOBILE NAVIGATION MENU

    const menuButton = document.querySelector(".mobile-menu-toggle");
    const navigation = document.querySelector(".nav-links");

    if (menuButton && navigation) {
        menuButton.addEventListener("click", function () {
            const isOpen = navigation.classList.toggle("show");

            menuButton.setAttribute(
                "aria-expanded",
                isOpen ? "true" : "false"
            );

            const icon = menuButton.querySelector("i");

            if (icon) {
                icon.classList.toggle("fa-bars", !isOpen);
                icon.classList.toggle("fa-xmark", isOpen);
            }
        });

        navigation.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                navigation.classList.remove("show");
                menuButton.setAttribute("aria-expanded", "false");

                const icon = menuButton.querySelector("i");

                if (icon) {
                    icon.classList.add("fa-bars");
                    icon.classList.remove("fa-xmark");
                }
            });
        });
    }

    console.log("NSBM NewsDesk homepage loaded successfully.");
});
