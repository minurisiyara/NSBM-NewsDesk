
/* =========================================
   NSBM NEWSDESK - HOMEPAGE SLIDESHOW
   File: assets/js/main.js
   ========================================= */

document.addEventListener("DOMContentLoaded", function () {

    // Find all featured-news slides on the homepage.
    const slides = document.querySelectorAll(".hero-slide");

    // Find the Previous and Next buttons.
    const previousButton = document.getElementById("previousSlide");
    const nextButton = document.getElementById("nextSlide");

    // Stop if the homepage has no slideshow.
    if (slides.length === 0) {
        return;
    }

    // Keep track of the currently displayed slide.
    let currentSlide = 0;

    // Display a particular slide.
    function showSlide(index) {

        // Hide all slides first.
        slides.forEach(function (slide) {
            slide.classList.remove("is-active");
        });

        // Make the selected slide visible.
        slides[index].classList.add("is-active");

        // Update the current slide number.
        currentSlide = index;
    }

    // Move to the next slide.
    function nextSlide() {
        const nextIndex = (currentSlide + 1) % slides.length;
        showSlide(nextIndex);
    }

    // Move to the previous slide.
    function previousSlide() {
        const previousIndex =
            (currentSlide - 1 + slides.length) % slides.length;

        showSlide(previousIndex);
    }

    // Connect the Next button to the nextSlide function.
    if (nextButton) {
        nextButton.addEventListener("click", nextSlide);
    }

    // Connect the Previous button to the previousSlide function.
    if (previousButton) {
        previousButton.addEventListener("click", previousSlide);
    }

    // Show the first slide when the page loads.
    showSlide(0);

    // Automatically change slides every 5 seconds.
    if (slides.length > 1) {
        setInterval(nextSlide, 5000);
    }

});
