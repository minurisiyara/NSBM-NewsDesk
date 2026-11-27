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

    // Pause on hover only for real mouse devices.
    // On touch screens a tap leaves a "sticky" hover that would stop autoplay forever.
    if (window.matchMedia('(hover: hover)').matches) {
        slider.addEventListener('mouseenter', stopAuto);
        slider.addEventListener('mouseleave', startAuto);
    }

    // Swipe left / right on touch screens
    let touchStartX = 0;
    let touchStartY = 0;

    slider.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].clientX;
        touchStartY = e.changedTouches[0].clientY;
        stopAuto();
    }, { passive: true });

    slider.addEventListener('touchend', (e) => {
        const dx = e.changedTouches[0].clientX - touchStartX;
        const dy = e.changedTouches[0].clientY - touchStartY;

        // horizontal swipe of at least 40px, and more horizontal than vertical
        if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
            showSlide(dx < 0 ? current + 1 : current - 1);
        }
        startAuto();
    }, { passive: true });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAuto();
        } else {
            startAuto();
        }
    });

    startAuto();
});