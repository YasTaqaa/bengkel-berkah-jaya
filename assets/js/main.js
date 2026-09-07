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
    new bootstrap.Carousel(heroEl, {
      interval: 3000,
      ride: 'carousel',
      wrap: true,
      pause: 'hover'
    });

    heroEl.addEventListener('slide.bs.carousel', function (e) {
      const nextSlide = heroEl.querySelectorAll('.carousel-item')[e.to];
      const animEls = nextSlide.querySelectorAll('.hero-badge, .hero-title, .hero-desc, .hero-buttons');

      animEls.forEach(function (el) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(28px)';
        el.style.animation = 'none';
      });

      document.querySelectorAll('.hero-indicators button').forEach(function (btn, i) {
        btn.classList.toggle('active', i === e.to);
      });
    });

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

  // ===========================
  // Modal Preview Galeri
  // ===========================
  const galeriModal = document.getElementById('galeriModal');
  const modalImg = document.getElementById('modalGaleriImg');
  const modalTitle = document.getElementById('modalGaleriTitle');
  const modalDesc = document.getElementById('modalGaleriDesc');

  if (galeriModal && modalImg && modalTitle && modalDesc) {
    galeriModal.addEventListener('show.bs.modal', function (event) {
      const trigger = event.relatedTarget;
      if (!trigger) return;

      const img = trigger.getAttribute('data-img') || '';
      const title = trigger.getAttribute('data-title') || 'Preview gambar';
      const desc = trigger.getAttribute('data-desc') || '';

      modalImg.src = img;
      modalImg.alt = title;
      modalTitle.textContent = title;
      modalDesc.innerHTML = desc ? desc.replace(/\n/g, '<br>') : 'Tidak ada deskripsi.';
    });

    modalImg.addEventListener('error', function () {
      modalImg.src = '';
      modalTitle.textContent = 'Gambar tidak tersedia';
      modalDesc.textContent = 'File gambar tidak ditemukan.';
    });

    galeriModal.addEventListener('hidden.bs.modal', function () {
      modalImg.src = '';
      modalImg.alt = '';
      modalTitle.textContent = '';
      modalDesc.textContent = '';
    });
  }

});

// ===========================
// Navbar scroll shadow
// ===========================
window.addEventListener('scroll', function () {
  const nav = document.getElementById('mainNav');
  if (nav) nav.classList.toggle('nav-scrolled', window.scrollY > 15);
});