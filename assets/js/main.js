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
modalDesc.innerHTML = desc ? desc.replace(/\n/g, '') : 'Tidak ada deskripsi.';
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

// ===========================
// Modal Preview Layanan
// ===========================
const layananModal = document.getElementById('layananModal');
const modalLayananImg = document.getElementById('modalLayananImg');
const modalLayananTitle = document.getElementById('modalLayananTitle');
const modalLayananDesc = document.getElementById('modalLayananDesc');

if (layananModal && modalLayananImg && modalLayananTitle && modalLayananDesc) {
layananModal.addEventListener('show.bs.modal', function (event) {
const trigger = event.relatedTarget;
if (!trigger) return;

const img = trigger.getAttribute('data-img') || '';
const title = trigger.getAttribute('data-title') || 'Preview layanan';
const desc = trigger.getAttribute('data-desc') || '';

modalLayananImg.src = img;
modalLayananImg.alt = title;
modalLayananTitle.textContent = title;
modalLayananDesc.innerHTML = desc ? desc.replace(/\n/g, '<br>') : 'Tidak ada deskripsi.';
});

modalLayananImg.addEventListener('error', function () {
modalLayananImg.src = '';
modalLayananTitle.textContent = 'Gambar tidak tersedia';
modalLayananDesc.textContent = 'File gambar tidak ditemukan.';
});

layananModal.addEventListener('hidden.bs.modal', function () {
modalLayananImg.src = '';
modalLayananImg.alt = '';
modalLayananTitle.textContent = '';
modalLayananDesc.textContent = '';
});
}

// ===========================
// Form Pesan — Pilih Model dari Galeri (kartu gambar, bukan <select>)
// Membaca window.dataGaleriPesan yang di-set inline oleh pesan.php
// ===========================
const layananSelect = document.getElementById('layananSelect');
const galeriWrap = document.getElementById('galeriPilihanWrap');
const galeriIdInput = document.getElementById('galeriIdInput');

if (layananSelect && galeriWrap && galeriIdInput) {
    const dataGaleri = window.dataGaleriPesan || [];
    const dataEstimasi = window.dataEstimasiPesan || [];
    const dataHargaLayanan = window.dataHargaLayananPesan || {};

    const estimasiPreview = document.getElementById('estimasiPreview');
    const estimasiPreviewRows = document.getElementById('estimasiPreviewRows');
    const estimasiModelBox = document.getElementById('estimasiModelBox');
    const estimasiModelNilai = document.getElementById('estimasiModelNilai');

    function formatRupiah(angka) {
        return 'Rp ' + Number(angka).toLocaleString('id-ID');
    }

    // Kotak biru: kisaran umum begitu layanan dipilih
    function updateEstimasiPreview() {
        const layananId = layananSelect.value;
        if (!estimasiPreview || !estimasiPreviewRows) return;

        if (!layananId) {
            estimasiPreview.classList.remove('show');
            return;
        }

        const baris = dataEstimasi.filter(function (e) { return e.layanan_id == layananId; });
        estimasiPreviewRows.innerHTML = '';

        if (baris.length > 0) {
            baris.forEach(function (e) {
                estimasiPreviewRows.innerHTML +=
                    '<div class="estimasi-preview-row">' +
                    '<span>' + e.ukuran_model + '</span>' +
                    '<span class="harga">' + formatRupiah(e.estimasi_harga) + ' /m2</span>' +
                    '</div>';
            });
        } else if (dataHargaLayanan[layananId]) {
            estimasiPreviewRows.innerHTML =
                '<div class="estimasi-preview-row">' +
                '<span>Model standar</span>' +
                '<span class="harga">Mulai ' + formatRupiah(dataHargaLayanan[layananId].harga) + ' /m2</span>' +
                '</div>';
        }

        estimasiPreview.classList.add('show');
    }

    // Kotak hijau: harga presisi untuk kartu model yang diklik
    function updateEstimasiModel(galeriId) {
        const layananId = layananSelect.value;
        if (!estimasiModelBox || !estimasiModelNilai) return;

        if (!galeriId) {
            estimasiModelBox.classList.remove('show');
            return;
        }

        const model = dataGaleri.find(function (g) { return g.id == galeriId; });

        if (model && model.harga) {
            estimasiModelNilai.textContent = formatRupiah(model.harga) + ' /m2';
            estimasiModelBox.classList.add('show');
        } else if (layananId && dataHargaLayanan[layananId]) {
            estimasiModelNilai.textContent = 'Mulai ' + formatRupiah(dataHargaLayanan[layananId].harga) + ' /m2';
            estimasiModelBox.classList.add('show');
        } else {
            estimasiModelBox.classList.remove('show');
        }
    }

    function renderGaleriPilihan() {
        const layananId = layananSelect.value;
        galeriIdInput.value = '';
        galeriWrap.innerHTML = '';
        updateEstimasiModel('');

        if (!layananId) {
            galeriWrap.innerHTML = '<p class="text-muted small mb-0">Pilih layanan terlebih dahulu untuk melihat model yang tersedia.</p>';
            return;
        }

        const filtered = dataGaleri.filter(function (g) { return g.layanan_id == layananId; });

        if (filtered.length === 0) {
            galeriWrap.innerHTML = '<p class="text-muted small mb-0">Belum ada model untuk layanan ini.</p>';
            return;
        }

        const grid = document.createElement('div');
        grid.className = 'galeri-pilih-grid';

        const noneCard = document.createElement('label');
        noneCard.className = 'galeri-pilih-card galeri-pilih-none active';
        noneCard.innerHTML =
            '<input type="radio" name="galeri_pilih_radio" value="" checked>' +
            '<div class="galeri-pilih-none-inner">' +
            '<i class="bi bi-slash-circle"></i><span>Tidak pilih model</span>' +
            '</div>';
        grid.appendChild(noneCard);

        filtered.forEach(function (g) {
            const card = document.createElement('label');
            card.className = 'galeri-pilih-card';
            card.innerHTML =
                '<input type="radio" name="galeri_pilih_radio" value="' + g.id + '">' +
                '<img src="assets/img/galeri/' + g.foto + '" alt="' + g.judul + '" loading="lazy" ' +
                'onerror="this.src=\'\'; this.alt=\'Gambar tidak tersedia\'">' +
                '<span class="galeri-pilih-title">' + g.judul + '</span>';
            grid.appendChild(card);
        });

        galeriWrap.appendChild(grid);

        grid.querySelectorAll('input[type=radio]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                galeriIdInput.value = this.value;
                grid.querySelectorAll('.galeri-pilih-card').forEach(function (c) {
                    c.classList.remove('active');
                });
                this.closest('.galeri-pilih-card').classList.add('active');
                updateEstimasiModel(this.value);
            });
        });
    }

    layananSelect.addEventListener('change', function () {
        renderGaleriPilihan();
        updateEstimasiPreview();
    });

    renderGaleriPilihan();
    updateEstimasiPreview();
}

});

// ===========================
// Navbar scroll shadow
// ===========================
window.addEventListener('scroll', function () {
const nav = document.getElementById('mainNav');
if (nav) nav.classList.toggle('nav-scrolled', window.scrollY > 15);
});