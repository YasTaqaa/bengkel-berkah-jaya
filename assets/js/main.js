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
// Modal Preview Galeri: carousel foto di dalam modal


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

    // Harga sekarang berupa teks bebas, misalnya:
    // "Rp 4.000.000 per paket", "Rp 750.000 per unit",
    // atau "Menyesuaikan kebutuhan".
    function formatHarga(harga) {
        if (harga === null || harga === undefined) {
            return '';
        }

        return String(harga).trim();
    }

    // Menghindari HTML dari database ditulis sebagai tag HTML.
    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    // Kotak biru: estimasi umum setelah layanan dipilih.
    function updateEstimasiPreview() {
        const layananId = layananSelect.value;

        if (!estimasiPreview || !estimasiPreviewRows) {
            return;
        }

        if (!layananId) {
            estimasiPreview.classList.remove('show');
            estimasiPreviewRows.innerHTML = '';
            return;
        }

        const baris = dataEstimasi.filter(function (e) {
            return String(e.layanan_id) === String(layananId);
        });

        estimasiPreviewRows.innerHTML = '';

        if (baris.length > 0) {
            baris.forEach(function (e) {
                const ukuranModel = escapeHtml(e.ukuran_model);
                const hargaEstimasi = escapeHtml(formatHarga(e.estimasi_harga));

                estimasiPreviewRows.innerHTML +=
                    '<div class="estimasi-preview-row">' +
                    '<span>' + ukuranModel + '</span>' +
                    '<span class="harga">' + hargaEstimasi + '</span>' +
                    '</div>';
            });
        } else if (
            dataHargaLayanan[layananId] &&
            formatHarga(dataHargaLayanan[layananId].harga) !== ''
        ) {
            estimasiPreviewRows.innerHTML =
                '<div class="estimasi-preview-row">' +
                '<span>Harga layanan</span>' +
                '<span class="harga">' +
                escapeHtml(formatHarga(dataHargaLayanan[layananId].harga)) +
                '</span>' +
                '</div>';
        } else {
            estimasiPreviewRows.innerHTML =
                '<div class="estimasi-preview-row">' +
                '<span>Informasi harga</span>' +
                '<span class="harga">Harga menyesuaikan kebutuhan</span>' +
                '</div>';
        }

        estimasiPreview.classList.add('show');
    }

    // Kotak hijau: harga spesifik ketika pelanggan memilih model galeri.
    function updateEstimasiModel(galeriId) {
        const layananId = layananSelect.value;

        if (!estimasiModelBox || !estimasiModelNilai) {
            return;
        }

        if (!galeriId) {
            estimasiModelBox.classList.remove('show');
            estimasiModelNilai.textContent = '';
            return;
        }

        const model = dataGaleri.find(function (g) {
            return String(g.id) === String(galeriId);
        });

        if (model && formatHarga(model.harga) !== '') {
            estimasiModelNilai.textContent = formatHarga(model.harga);
            estimasiModelBox.classList.add('show');
        } else if (
            layananId &&
            dataHargaLayanan[layananId] &&
            formatHarga(dataHargaLayanan[layananId].harga) !== ''
        ) {
            estimasiModelNilai.textContent =
                formatHarga(dataHargaLayanan[layananId].harga);

            estimasiModelBox.classList.add('show');
        } else {
            estimasiModelBox.classList.remove('show');
            estimasiModelNilai.textContent = '';
        }
    }

    function renderGaleriPilihan() {
        const layananId = layananSelect.value;

        galeriIdInput.value = '';
        galeriWrap.innerHTML = '';
        updateEstimasiModel('');

        if (!layananId) {
            galeriWrap.innerHTML =
                '<p class="text-muted small mb-0">' +
                'Pilih layanan terlebih dahulu untuk melihat model yang tersedia.' +
                '</p>';
            return;
        }

        const filtered = dataGaleri.filter(function (g) {
            const layananGaleri =
                g.layanan_id ?? g.layananid ?? g.layananId;

            return String(layananGaleri) === String(layananId);
        });

        if (filtered.length === 0) {
            galeriWrap.innerHTML =
                '<p class="text-muted small mb-0">' +
                'Belum ada model untuk layanan ini.' +
                '</p>';
            return;
        }

        const grid = document.createElement('div');
        grid.className = 'galeri-pilih-grid';

        const noneCard = document.createElement('label');
        noneCard.className = 'galeri-pilih-card galeri-pilih-none active';
        noneCard.innerHTML =
            '<input type="radio" name="galeri_pilih_radio" value="" checked>' +
            '<div class="galeri-pilih-none-inner">' +
            '<i class="bi bi-slash-circle"></i>' +
            '<span>Tidak pilih model</span>' +
            '</div>';

        grid.appendChild(noneCard);

        filtered.forEach(function (g) {
            const card = document.createElement('label');
            const judul = escapeHtml(g.judul ?? 'Model galeri');
            const foto = encodeURI(String(g.foto ?? ''));

            card.className = 'galeri-pilih-card';
            card.innerHTML =
                '<input type="radio" name="galeri_pilih_radio" value="' +
                escapeHtml(g.id) + '">' +
                '<img src="assets/img/galeri/' + foto + '"' +
                ' alt="' + judul + '" loading="lazy"' +
                ' onerror="this.src=\'\'; this.alt=\'Gambar tidak tersedia\'">' +
                '<span class="galeri-pilih-title">' + judul + '</span>';

            grid.appendChild(card);
        });

        galeriWrap.appendChild(grid);

        grid.querySelectorAll('input[type="radio"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                galeriIdInput.value = this.value;

                grid.querySelectorAll('.galeri-pilih-card').forEach(function (card) {
                    card.classList.remove('active');
                });

                const parentCard = this.closest('.galeri-pilih-card');

                if (parentCard) {
                    parentCard.classList.add('active');
                }

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

(function () {
    function initGaleriThumbnailModal() {
        const modal = document.getElementById('galeriModal');
        const modalImg = document.getElementById('modalGaleriImg');
        const thumbnailList = document.getElementById('modalThumbnailList');
        const modalTitle = document.getElementById('modalGaleriTitle');
        const modalDesc = document.getElementById('modalGaleriDesc');

        if (!modal || !modalImg || !thumbnailList) {
            return;
        }

        modal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;

            if (!trigger) {
                return;
            }

            let fotoList = [];

            try {
                fotoList = JSON.parse(
                    trigger.getAttribute('data-foto-list') || '[]'
                );
            } catch (error) {
                console.error('Data foto tidak valid:', error);
            }

            thumbnailList.innerHTML = '';

            if (modalTitle) {
                modalTitle.textContent =
                    trigger.getAttribute('data-title') || '';
            }

            if (modalDesc) {
                modalDesc.textContent =
                    trigger.getAttribute('data-desc') || '';
            }

            if (fotoList.length === 0) {
                modalImg.removeAttribute('src');
                modalImg.alt = 'Gambar tidak tersedia';
                return;
            }

            modalImg.src = fotoList[0];
            modalImg.alt = modalTitle
                ? modalTitle.textContent
                : 'Preview gambar';

            fotoList.forEach(function (foto, index) {
                const thumbnailButton = document.createElement('button');

                thumbnailButton.type = 'button';
                thumbnailButton.className =
                    'modal-thumbnail' + (index === 0 ? ' active' : '');

                thumbnailButton.setAttribute(
                    'aria-label',
                    'Tampilkan foto ' + (index + 1)
                );

                const thumbnailImage = document.createElement('img');
                thumbnailImage.src = foto;
                thumbnailImage.alt = 'Foto ' + (index + 1);

                thumbnailButton.appendChild(thumbnailImage);
                thumbnailList.appendChild(thumbnailButton);

                thumbnailButton.addEventListener('click', function (clickEvent) {
                    clickEvent.preventDefault();
                    clickEvent.stopPropagation();

                    modalImg.src = foto;

                    thumbnailList
                        .querySelectorAll('.modal-thumbnail')
                        .forEach(function (button) {
                            button.classList.remove('active');
                        });

                    thumbnailButton.classList.add('active');
                });
            });
        });

        modal.addEventListener('hidden.bs.modal', function () {
            modalImg.removeAttribute('src');
            modalImg.alt = '';
            thumbnailList.innerHTML = '';

            if (modalTitle) {
                modalTitle.textContent = '';
            }

            if (modalDesc) {
                modalDesc.textContent = '';
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initGaleriThumbnailModal
        );
    } else {
        initGaleriThumbnailModal();
    }
})();

// =============================================
// Preview upload foto referensi pada form pesan
// =============================================
function initPesanReferensi() {
    const inputReferensi =
        document.getElementById('inputReferensi');

    const referensiPreview =
        document.getElementById('referensiPreview');

    if (!inputReferensi || !referensiPreview) {
        return;
    }

    inputReferensi.addEventListener('change', function () {
        referensiPreview.innerHTML = '';

        const files = Array.from(inputReferensi.files).slice(0, 3);

        const tipeDiizinkan = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        const maksimalUkuran = 3 * 1024 * 1024;

        if (inputReferensi.files.length > 3) {
            alert('Maksimal 3 gambar referensi.');
        }

        files.forEach(function (file) {
            if (!tipeDiizinkan.includes(file.type)) {
                return;
            }

            if (file.size > maksimalUkuran) {
                return;
            }

            const reader = new window.FileReader();

            reader.onload = function (event) {
                const img = document.createElement('img');

                img.src = event.target.result;
                img.alt = 'Preview gambar referensi pelanggan';

                referensiPreview.appendChild(img);
            };

            reader.readAsDataURL(file);
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initPesanReferensi
    );
} else {
    initPesanReferensi();
}