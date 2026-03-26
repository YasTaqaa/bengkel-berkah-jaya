<?php require_once __DIR__ . "/config.php"; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Bengkel Las Berkah Jaya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include "navbar.php"; ?>
    <?php include "hero-carousel.php"; ?>

    <!-- KEUNGGULAN -->
    <section class="py-5" id="keunggulan">
        <div class="container">
            <div class="text-center mb-5 reveal">
                <span class="section-label">Mengapa Memilih Kami</span>
                <h2 class="section-title mb-0">Keunggulan Bengkel Las Berkah Jaya</h2>
            </div>
            <div class="row text-center g-4">
                <div class="col-md-4 reveal">
                    <div class="keunggulan-card">
                        <div class="keunggulan-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h5>Bahan Berkualitas</h5>
                        <p>Menggunakan baja ringan dan besi pilihan untuk hasil kuat dan tahan lama.</p>
                    </div>
                </div>
                <div class="col-md-4 reveal">
                    <div class="keunggulan-card">
                        <div class="keunggulan-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h5>Tepat Waktu</h5>
                        <p>Tim berpengalaman memastikan proyek selesai sesuai jadwal yang disepakati.</p>
                    </div>
                </div>
                <div class="col-md-4 reveal">
                    <div class="keunggulan-card">
                        <div class="keunggulan-icon">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <h5>Harga Transparan</h5>
                        <p>Estimasi biaya jelas di awal tanpa biaya tersembunyi.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- LAYANAN POPULER -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5 reveal">
                <span class="section-label">Apa yang Kami Kerjakan</span>
                <h2 class="section-title mb-0">Layanan Populer</h2>
            </div>
            <div class="row g-4">
                <?php
                $qLayanan = mysqli_query($conn, "SELECT * FROM layanan ORDER BY urutan ASC LIMIT 3");
                while ($lay = mysqli_fetch_assoc($qLayanan)) {
                ?>
                <div class="col-md-4 reveal">
                    <div class="card service-card h-100">
                        <img src="assets/img/layanan/<?php echo htmlspecialchars($lay['foto']); ?>" class="card-img-top"
                            style="height:200px;" alt="<?php echo htmlspecialchars($lay['nama']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($lay['nama']); ?></h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($lay['deskripsi'])); ?></p>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <strong>Mulai Rp <?php echo number_format($lay['harga'],0,',','.'); ?></strong>
                            <a href="layanan-detail.php?id=<?php echo $lay['id']; ?>"
                                class="btn btn-sm btn-outline-primary">Detail</a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="text-center mt-5 reveal">
                <a href="layanan.php" class="btn-lihat-semua">
                    <i class="bi bi-grid-3x3-gap"></i> Lihat Semua Layanan
                </a>
            </div>
        </div>
    </section>

    <!-- TIM -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5 reveal">
                <span class="section-label">Orang-orang di Baliknya</span>
                <h2 class="section-title mb-0">Tim Bengkel</h2>
            </div>
            <div class="row g-4">
                <?php
                $qTim = mysqli_query($conn, "SELECT * FROM karyawan ORDER BY urutan ASC LIMIT 3");
                while ($t = mysqli_fetch_assoc($qTim)) {
                ?>
                <div class="col-md-4 reveal">
                    <div class="card team-card h-100 text-center">
                        <img src="assets/img/tim/<?php echo htmlspecialchars($t['foto']); ?>" class="card-img-top"
                            style="height:250px;" alt="<?php echo htmlspecialchars($t['nama']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($t['nama']); ?></h5>
                            <p class="mb-1 text-muted"><?php echo htmlspecialchars($t['jabatan']); ?></p>
                            <span class="badge-pengalaman">
                                <i class="bi bi-star-fill me-1"></i><?php echo (int)$t['pengalaman']; ?> Tahun
                                Pengalaman
                            </span>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <!-- GALERI -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5 reveal">
                <span class="section-label">Hasil Karya Kami</span>
                <h2 class="section-title mb-0">Galeri Proyek Terbaru</h2>
            </div>
            <?php
            $qGal = mysqli_query($conn, "SELECT * FROM galeri ORDER BY id DESC LIMIT 6");
            $galeri = [];
            while ($g = mysqli_fetch_assoc($qGal)) { $galeri[] = $g; }
            if (count($galeri) > 0) {
            ?>
            <div id="galeriCarousel" class="carousel slide reveal" data-bs-ride="carousel">
                <div class="carousel-inner rounded-3 overflow-hidden shadow">
                    <?php foreach ($galeri as $idx => $g) { ?>
                    <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                        <img src="assets/img/galeri/<?php echo htmlspecialchars($g['foto']); ?>"
                            class="d-block w-100 gallery-img" style="height:420px;"
                            alt="<?php echo htmlspecialchars($g['judul']); ?>">
                        <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
                            <h5><?php echo htmlspecialchars($g['judul']); ?></h5>
                            <p><?php echo htmlspecialchars($g['keterangan']); ?></p>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#galeriCarousel"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#galeriCarousel"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
            <?php } else { ?>
            <p class="text-center text-muted">Belum ada data galeri.</p>
            <?php } ?>
            <div class="text-center mt-5 reveal">
                <a href="galeri.php" class="btn-lihat-semua">
                    <i class="bi bi-images"></i> Lihat Semua Galeri
                </a>
            </div>
        </div>
    </section>

    <!-- KONTAK -->
    <section class="py-5 kontak-section">
        <div class="container">
            <div class="text-center mb-5 reveal">
                <span class="section-label-light">Hubungi Kami</span>
                <h2 class="section-title mb-0 text-white">Kontak & Lokasi Bengkel</h2>
            </div>
            <div class="row g-4 align-items-stretch">
                <div class="col-md-5 reveal">
                    <div class="kontak-card h-100">
                        <h5 class="kontak-card-title"><i class="bi bi-info-circle me-2"></i>Informasi Kontak</h5>
                        <ul class="kontak-list">
                            <li>
                                <span class="kontak-icon"><i class="bi bi-geo-alt-fill"></i></span>
                                <span>Pejagoan, Kec. Pejagoan, Kabupaten Kebumen, Jawa Tengah</span>
                            </li>
                            <li>
                                <span class="kontak-icon"><i class="bi bi-whatsapp"></i></span>
                                <span>+62 812‑3456‑7890</span>
                            </li>
                            <li>
                                <span class="kontak-icon"><i class="bi bi-envelope-fill"></i></span>
                                <span>bengkelberkahjaya@email.com</span>
                            </li>
                            <li>
                                <span class="kontak-icon"><i class="bi bi-clock-fill"></i></span>
                                <span>Senin – Sabtu, 08.00 – 16.00 WIB</span>
                            </li>
                        </ul>
                        <a href="pesan.php" class="btn-hero-primary d-inline-flex mt-3">
                            <i class="bi bi-calendar2-check me-2"></i> Pesan & Survey Gratis
                        </a>
                    </div>
                </div>
                <div class="col-md-7 reveal">
                    <div class="ratio ratio-16x9 h-100 rounded-3 overflow-hidden shadow">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7908.190072761391!2d109.64428496625673!3d-7.672931715987684!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7aca0ee1454c0d%3A0xee5d606e0611168e!2sBerkah%20jaya!5e0!3m2!1sid!2sid!4v1770822119606!5m2!1sid!2sid"
                            style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WA FLOATING -->
    <div class="floating-wa">
        <a href="https://wa.me/6281234567890" target="_blank" title="Chat WhatsApp">
            <i class="bi bi-whatsapp"></i>
        </a>
    </div>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <strong class="footer-brand">Berkah Jaya</strong>
                    <span class="footer-sep">·</span>
                    <span>Bengkel Las & Kanopi, Kebumen</span>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small>&copy; <?php echo date('Y'); ?> Bengkel Las Berkah Jaya. All rights reserved.</small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>