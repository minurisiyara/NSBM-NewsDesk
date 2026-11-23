document.addEventListener('DOMContentLoaded', () => {
    const slider = document.getElementById('featuredSlideshow');
    if (!slider) return;

    const slides = slider.querySelectorAll('.featured-slide');
    const dots = slider.querySelectorAll('.slide-dot');
    const prevBtn = document.getElementById('prevFeaturedBtn');
    const nextBtn = document.getElementById('nextFeaturedBtn');

    if (slides.length <= 1) return;

    let current = 0;
    let timer = null;
    const AUTO_SLIDE_INTERVAL = 3500;

    function showSlide(idx) {
        current = (idx + slides.length) % slides.length;
        slides.forEach((slide, i) => slide.classList.toggle('is-active', i === current));
        dots.forEach((dot, i) => dot.classList.toggle('is-active', i === current));
    }

    function startAuto() {
        stopAuto();
        timer = setInterval(() => {
            showSlide(current + 1);
        }, AUTO_SLIDE_INTERVAL);
    }

    function stopAuto() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }

    prevBtn?.addEventListener('click', () => {
        showSlide(current - 1);
        startAuto();
    });

    nextBtn?.addEventListener('click', () => {
        showSlide(current + 1);
        startAuto();
    });

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            showSlide(Number(dot.dataset.slide));
            startAuto();
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            showSlide(current - 1);
            startAuto();
        } else if (e.key === 'ArrowRight') {
            showSlide(current + 1);
            startAuto();
        }
    });

    slider.addEventListener('mouseenter', stopAuto);
    slider.addEventListener('mouseleave', startAuto);

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAuto();
        } else {
            startAuto();
        }
    });

    startAuto();
});