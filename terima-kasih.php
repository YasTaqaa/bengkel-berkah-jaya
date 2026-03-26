<?php require_once __DIR__ . "/config.php"; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Terima Kasih - Bengkel Las Berkah Jaya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="no-hero">
    <?php include "navbar.php"; ?>

    <section class="py-5 thankyou-section">
        <div class="container">
            <div class="thankyou-card text-center reveal">
                <div class="thankyou-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h1 class="thankyou-title">Terima Kasih!</h1>
                <p class="thankyou-desc">Data pemesanan Anda sudah kami terima.<br>Kami akan menghubungi Anda segera
                    untuk konfirmasi jadwal survey.</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap mt-4">
                    <a href="index.php" class="btn-lihat-semua">
                        <i class="bi bi-house me-2"></i>Kembali ke Beranda
                    </a>
                    <a href="https://wa.me/6281234567890" target="_blank" class="btn-hero-primary">
                        <i class="bi bi-whatsapp me-2"></i>Chat WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </section>

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