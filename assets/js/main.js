document.addEventListener('DOMContentLoaded', function () {

    // ===========================
    // Scroll Reveal Animation
    // ===========================
    const revealEls = document.querySelectorAll('.reveal');

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry, i) {
            if (entry.isIntersecting) {
                setTimeout(function () {
                    entry.target.classList.add('visible');
                }, i * 80);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    revealEls.forEach(function (el) {
        observer.observe(el);
    });

    // ===========================
    // Hero Carousel — auto slide + animasi teks
    // ===========================
    const heroEl = document.getElementById('heroCarousel');
    if (heroEl) {
        const heroCarousel = new bootstrap.Carousel(heroEl, {
            interval: 3000,
            ride: 'carousel',
            wrap: true,
            pause: 'hover'
        });

        // Sebelum slide berganti — sembunyikan teks slide berikutnya
        heroEl.addEventListener('slide.bs.carousel', function (e) {
            const nextSlide = heroEl.querySelectorAll('.carousel-item')[e.to];
            const animEls = nextSlide.querySelectorAll('.hero-badge, .hero-title, .hero-desc, .hero-buttons');
            animEls.forEach(function (el) {
                el.style.opacity = '0';
                el.style.transform = 'translateY(28px)';
                el.style.animation = 'none';
            });

            // Sync indikator
            document.querySelectorAll('.hero-indicators button').forEach(function (btn, i) {
                btn.classList.toggle('active', i === e.to);
            });
        });

        // Setelah slide masuk — jalankan animasi teks
        heroEl.addEventListener('slid.bs.carousel', function () {
            const activeSlide = heroEl.querySelector('.carousel-item.active');
            const animEls = activeSlide.querySelectorAll('.hero-badge, .hero-title, .hero-desc, .hero-buttons');
            animEls.forEach(function (el, i) {
                el.style.animation = 'none';
                el.offsetHeight; 
                el.style.opacity = '';
                el.style.transform = '';
                el.style.animation = `heroFadeUp 0.6s ${i * 0.1}s ease both`;
            });
        });
    }

    // ===========================
    // Galeri Carousel
    // ===========================
    const galeriEl = document.getElementById('galeriCarousel');
    if (galeriEl) {
        new bootstrap.Carousel(galeriEl, {
            interval: 3500,
            ride: 'carousel',
            wrap: true,
            pause: 'hover'
        });
    }

    // ===========================
    // Tooltip Bootstrap
    // ===========================
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });

    // ===========================
    // Auto-dismiss alert
    // ===========================
    document.querySelectorAll('.alert.alert-dismissible').forEach(function (alert) {
        setTimeout(function () {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        }, 4000);
    });

});

// ===========================
// Navbar scroll shadow
// ===========================
window.addEventListener('scroll', function () {
    const nav = document.getElementById('mainNav');
    if (nav) nav.classList.toggle('nav-scrolled', window.scrollY > 15);
});