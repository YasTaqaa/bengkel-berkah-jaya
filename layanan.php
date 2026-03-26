<?php require_once __DIR__ . "/config.php"; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Layanan - Bengkel Las Berkah Jaya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="no-hero">
    <?php include "navbar.php"; ?>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="container">
            <div class="page-header-inner">
                <span class="section-label-light">Yang Kami Tawarkan</span>
                <h1 class="page-header-title">Daftar Layanan</h1>
                <p class="page-header-desc">Temukan layanan yang sesuai kebutuhan Anda, gratis survey untuk area
                    Kebumen.</p>
            </div>
        </div>
    </div>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <?php
                $qLayanan = mysqli_query($conn, "SELECT * FROM layanan ORDER BY urutan ASC");
                while ($lay = mysqli_fetch_assoc($qLayanan)) {
                ?>
                <div class="col-md-4 reveal">
                    <div class="card service-card h-100">
                        <img src="assets/img/layanan/<?php echo htmlspecialchars($lay['foto']); ?>" class="card-img-top"
                            style="height:210px;" alt="<?php echo htmlspecialchars($lay['nama']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($lay['nama']); ?></h5>
                            <p class="card-text text-muted"><?php echo nl2br(htmlspecialchars($lay['deskripsi'])); ?>
                            </p>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-primary">Mulai Rp
                                    <?php echo number_format($lay['harga'],0,',','.'); ?></strong>
                                <div class="d-flex gap-2">
                                    <a href="layanan-detail.php?id=<?php echo $lay['id']; ?>"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-images me-1"></i>Galeri
                                    </a>
                                    <a href="pesan.php?layanan_id=<?php echo $lay['id']; ?>"
                                        class="btn btn-sm btn-primary">
                                        <i class="bi bi-calendar2-check me-1"></i>Pesan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <!-- CTA Banner -->
    <section class="cta-banner">
        <div class="container text-center">
            <h3 class="cta-title">Belum yakin pilih layanan mana?</h3>
            <p class="cta-desc">Hubungi kami untuk konsultasi gratis, kami siap bantu tentukan solusi terbaik.</p>
            <a href="https://wa.me/6281234567890" target="_blank" class="btn-hero-primary">
                <i class="bi bi-whatsapp"></i> Chat WhatsApp Sekarang
            </a>
        </div>
    </section>

    <div class="floating-wa">
        <a href="https://wa.me/6281234567890" target="_blank"><i class="bi bi-whatsapp"></i></a>
    </div>

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