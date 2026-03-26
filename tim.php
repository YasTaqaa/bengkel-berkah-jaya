<?php require_once __DIR__ . "/config.php"; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tim Bengkel - Bengkel Las Berkah Jaya</title>
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
                <span class="section-label-light">Orang-orang di Baliknya</span>
                <h1 class="page-header-title">Tim Bengkel Las Berkah Jaya</h1>
                <p class="page-header-desc">Tenaga ahli berpengalaman yang siap mengerjakan proyek Anda dengan
                    profesional.</p>
            </div>
        </div>
    </div>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <?php
                $qTim = mysqli_query($conn, "SELECT * FROM karyawan ORDER BY urutan ASC");
                while ($t = mysqli_fetch_assoc($qTim)) {
                ?>
                <div class="col-md-4 reveal">
                    <div class="card team-card h-100 text-center">
                        <img src="assets/img/tim/<?php echo htmlspecialchars($t['foto']); ?>" class="card-img-top"
                            style="height:260px;" alt="<?php echo htmlspecialchars($t['nama']); ?>">
                        <div class="card-body">
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($t['nama']); ?></h5>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($t['jabatan']); ?></p>
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

    <!-- CTA Banner -->
    <section class="cta-banner">
        <div class="container text-center">
            <h3 class="cta-title">Tim kami siap mengerjakan proyek Anda</h3>
            <p class="cta-desc">Hubungi kami sekarang untuk konsultasi dan survey gratis.</p>
            <a href="pesan.php" class="btn-hero-primary">
                <i class="bi bi-calendar2-check"></i> Pesan & Survey Gratis
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