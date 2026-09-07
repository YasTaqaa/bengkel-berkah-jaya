<?php require_once __DIR__ . "/config.php"; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Galeri - Bengkel Las Berkah Jaya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="no-hero">
    <?php include "navbar.php"; ?>

    <div class="page-header">
        <div class="container">
            <div class="page-header-inner">
                <span class="section-label-light">Portofolio Kami</span>
                <h1 class="page-header-title">Galeri Proyek</h1>
                <p class="page-header-desc">Dokumentasi hasil pengerjaan.</p>
            </div>
        </div>
    </div>

    <section class="py-5">
        <div class="container">
            <?php
            $qGal = mysqli_query($conn, "SELECT * FROM galeri ORDER BY tgl_selesai DESC, id DESC");
            if (mysqli_num_rows($qGal) == 0) {
                echo '<p class="text-center text-muted py-5">Belum ada data galeri.</p>';
            } else {
            ?>
            <div class="row g-4">
                <?php while ($g = mysqli_fetch_assoc($qGal)) { ?>
                <div class="col-md-4 reveal">
                    <div class="card galeri-card h-100">
                        <div class="galeri-img-wrap" data-bs-toggle="modal" data-bs-target="#galeriModal"
                            data-img="assets/img/galeri/<?php echo htmlspecialchars($g['foto']); ?>"
                            data-title="<?php echo htmlspecialchars($g['judul']); ?>"
                            data-desc="<?php echo htmlspecialchars($g['keterangan']); ?>">

                            <img src="assets/img/galeri/<?php echo htmlspecialchars($g['foto']); ?>"
                                alt="<?php echo htmlspecialchars($g['judul']); ?>"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                            <div class="galeri-no-image" style="display:none;">
                                Gambar tidak tersedia
                            </div>

                            <div class="galeri-overlay">
                                <i class="bi bi-zoom-in"></i>
                            </div>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($g['judul']); ?></h5>
                            <p class="card-text text-muted"><?php echo nl2br(htmlspecialchars($g['keterangan'])); ?></p>
                        </div>

                        <div class="card-footer d-flex align-items-center gap-2">
                            <i class="bi bi-calendar3 text-muted"></i>
                            <small class="text-muted">
                                Selesai: <?php echo htmlspecialchars($g['tgl_selesai']); ?>
                            </small>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
    </section>

    <section class="cta-banner">
        <div class="container text-center">
            <h3 class="cta-title">Tertarik dengan hasil kerja kami?</h3>
            <p class="cta-desc">
                Kalau ada model yang cocok atau mau request desain sendiri,
                tinggal hubungi kami lewat WhatsApp.
            </p>
            <a href="https://wa.me/6281234567890" target="_blank" class="btn-hero-primary">
                <i class="bi bi-whatsapp"></i> Chat WhatsApp Sekarang
            </a>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <strong class="footer-brand">Berkah Jaya</strong>
                    <span class="footer-sep">·</span>
                    <span>Bengkel Las, Pejagoan, Kebumen</span>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small>&copy; <?php echo date('Y'); ?> Bengkel Las Berkah Jaya.</small>
                </div>
            </div>
        </div>
    </footer>

    <div class="modal fade modal-galeri" id="galeriModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-body p-0 text-center position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                    <img id="modalGaleriImg" src="" alt="" class="modal-preview-img">
                    <div class="modal-caption px-3 pb-2">
                        <h5 id="modalGaleriTitle"></h5>
                        <p id="modalGaleriDesc"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>